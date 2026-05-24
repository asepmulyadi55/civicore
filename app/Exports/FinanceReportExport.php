<?php

namespace App\Exports;

use App\Models\FinanceReport;
use App\Models\FinanceTransaction;
use App\Models\PaymentRecord;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinanceReportExport implements
    FromCollection,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected FinanceReport $report;
    protected string $currency;

    /** Number of title rows inserted before data (LAPORAN KEUANGAN header block). */
    private const TITLE_OFFSET = 5;

    // Collection-relative row indices (1-based); resolved in collection(), used in AfterSheet.
    protected int $jumlahCollRow    = 0;
    protected int $saldoCollRow     = 0;
    protected int $tabHdrCollRow    = 0;
    protected int $tabColCollRow    = 0;
    protected int $tabJumlahCollRow = 0;
    protected int $tabSaldoCollRow  = 0;

    public function __construct(FinanceReport $report)
    {
        $this->report   = $report;
        $this->currency = Setting::get('currency_symbol', 'Rp');
    }

    public function title(): string
    {
        return 'Laporan Keuangan';
    }

    // ── Indonesian month names ────────────────────────────────────────────────

    private function monthName(int $month): string
    {
        return ['Januari','Februari','Maret','April','Mei','Juni',
                'Juli','Agustus','September','Oktober','November','Desember'][$month - 1];
    }

    private function periodLabel(int $month, int $year): string
    {
        return $this->monthName($month) . ' ' . $year;
    }

    // ── Currency formatter ────────────────────────────────────────────────────

    private function fmt(float|int|string $n): string
    {
        return $this->currency . ' ' . number_format((float) $n, 0, ',', '.');
    }

    // ── Collection (all rows) ─────────────────────────────────────────────────

    public function collection(): Collection
    {
        $rows = collect();
        $no   = 1;

        // Row 1 of collection = spreadsheet row (1 + TITLE_OFFSET) after AfterSheet inserts title rows.
        // Column order: NO | TANGGAL | URAIAN | PENERIMAAN | PENGELUARAN | KETERANGAN

        // ── Column header row ─────────────────────────────────────────────────
        $rows->push(['NO', 'TANGGAL', 'URAIAN', 'PENERIMAAN', 'PENGELUARAN', 'KETERANGAN']);

        // ── Opening balance (first PENERIMAAN row) ────────────────────────────
        $prevMonth = $this->report->month === 1 ? 12 : $this->report->month - 1;
        $prevYear  = $this->report->month === 1 ? $this->report->year - 1 : $this->report->year;
        $openDate  = Carbon::create($this->report->year, $this->report->month, 1)->format('d/m/Y');

        $rows->push([
            $no++,
            $openDate,
            'Diterima saldo bulan ' . $this->periodLabel($prevMonth, $prevYear),
            $this->fmt($this->report->opening_balance),
            '-',
            '',
        ]);

        // ── Approved resident payment records — one aggregated row per payment_month ──
        // Group by payment_month so "3 residents paying Apr 2026" → one line with total + count.
        $payments = PaymentRecord::where('status', 'approved')
            ->whereNotNull('approved_at')
            ->whereYear('approved_at', $this->report->year)
            ->whereMonth('approved_at', $this->report->month)
            ->select(
                DB::raw('DATE_FORMAT(payment_month, "%Y-%m-01") as period'),
                DB::raw('COUNT(DISTINCT resident_id) as resident_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('MIN(approved_at) as first_approved_at')
            )
            ->groupBy(DB::raw('DATE_FORMAT(payment_month, "%Y-%m-01")'))
            ->orderBy(DB::raw('DATE_FORMAT(payment_month, "%Y-%m-01")'))
            ->get();

        foreach ($payments as $pg) {
            $period     = Carbon::parse($pg->period);
            $periodName = $this->periodLabel((int) $period->month, (int) $period->year);
            $approvedAt = Carbon::parse($pg->first_approved_at)->format('d/m/Y');
            $rows->push([
                $no++,
                $approvedAt,
                'Diterima Iuran dari Warga bulan ' . $periodName . ' (' . $pg->resident_count . ' KK)',
                $this->fmt($pg->total_amount),
                '-',
                '',
            ]);
        }

        // ── Manual transactions (income then expense, ordered by date) ─────────
        $transactions = FinanceTransaction::where('report_month', $this->report->month)
            ->where('report_year', $this->report->year)
            ->orderBy('transaction_date')
            ->get();

        foreach ($transactions as $tx) {
            $rows->push([
                $no++,
                $tx->transaction_date->format('d/m/Y'),
                $tx->description,
                $tx->type === 'income'  ? $this->fmt($tx->amount) : '-',
                $tx->type === 'expense' ? $this->fmt($tx->amount) : '-',
                $tx->notes ?? '',
            ]);
        }

        // ── JUMLAH row ────────────────────────────────────────────────────────
        // PENERIMAAN total = opening_balance + total_income (matches reference)
        $totalPenerimaan = (float) $this->report->opening_balance + (float) $this->report->total_income;
        $this->jumlahCollRow = $rows->count() + 1;
        $rows->push([
            '', 'JUMLAH', '',
            $this->fmt($totalPenerimaan),
            $this->fmt($this->report->total_expense),
            '',
        ]);

        // ── SALDO KAS row ─────────────────────────────────────────────────────
        $this->saldoCollRow = $rows->count() + 1;
        $rows->push([
            '', 'SALDO KAS bulan ' . $this->periodLabel($this->report->month, $this->report->year),
            '', '', $this->fmt($this->report->closing_balance), '',
        ]);

        // ── Blank separator ───────────────────────────────────────────────────
        $rows->push(['', '', '', '', '', '']);
        $rows->push(['', '', '', '', '', '']);

        // ── CATATAN TABUNGAN section ──────────────────────────────────────────
        $communityName = Setting::get('community_name', 'RT');
        $this->tabHdrCollRow = $rows->count() + 1;
        $rows->push(['', 'Catatan Tabungan yang ada di Bendahara Umum ' . $communityName . ' :', '', '', '', '']);

        $this->tabColCollRow = $rows->count() + 1;
        $rows->push(['NO', 'TANGGAL', 'URAIAN', 'PENERIMAAN', 'PENGELUARAN', 'KETERANGAN']);

        // Savings transactions: notes or category contains "tabungan" (case-insensitive)
        $savingsTx = FinanceTransaction::where('report_month', $this->report->month)
            ->where('report_year', $this->report->year)
            ->where(function ($q) {
                $q->whereRaw("LOWER(COALESCE(notes, '')) LIKE ?", ['%tabungan%'])
                  ->orWhereRaw("LOWER(COALESCE(category, '')) LIKE ?", ['%tabungan%']);
            })
            ->orderBy('transaction_date')
            ->get();

        $sNo      = 1;
        $sIncome  = 0.0;
        $sExpense = 0.0;

        foreach ($savingsTx as $tx) {
            $inc = $tx->type === 'income'  ? (float) $tx->amount : 0.0;
            $exp = $tx->type === 'expense' ? (float) $tx->amount : 0.0;
            $sIncome  += $inc;
            $sExpense += $exp;
            $rows->push([
                $sNo++,
                $tx->transaction_date->format('d/m/Y'),
                $tx->description,
                $inc > 0 ? $this->fmt($inc) : '-',
                $exp > 0 ? $this->fmt($exp) : '-',
                $tx->notes ?? '',
            ]);
        }

        if ($savingsTx->isEmpty()) {
            $rows->push(['', 'Tidak ada catatan tabungan', '', '', '', '']);
        }

        $this->tabJumlahCollRow = $rows->count() + 1;
        $rows->push(['', 'JUMLAH', '', $this->fmt($sIncome), $this->fmt($sExpense), '']);

        $this->tabSaldoCollRow = $rows->count() + 1;
        $rows->push([
            '', 'SALDO TABUNGAN bulan ' . $this->periodLabel($this->report->month, $this->report->year),
            '', '', $this->fmt($sIncome - $sExpense), '',
        ]);

        return $rows;
    }

    // ── AfterSheet styling ────────────────────────────────────────────────────

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = 'F';
                $off     = self::TITLE_OFFSET; // rows to prepend

                // ── Prepend 5 title rows ──────────────────────────────────────
                $sheet->insertNewRowBefore(1, $off);

                $communityName    = Setting::get('community_name', 'DWIPAPURI RESIDENTIAL COMMUNITY');
                $communityAddress = Setting::get('community_address', '');
                $monthId          = strtoupper($this->monthName($this->report->month));
                $year             = $this->report->year;

                // Row 1: LAPORAN KEUANGAN
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN KEUANGAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 2: Community name
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', strtoupper($communityName));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 3: Address (omit row if empty)
                $sheet->mergeCells("A3:{$lastCol}3");
                if ($communityAddress) {
                    $sheet->setCellValue('A3', strtoupper($communityAddress));
                }
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['bold' => false, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 4: BULAN … TAHUN …
                $sheet->mergeCells("A4:{$lastCol}4");
                $sheet->setCellValue('A4', "BULAN {$monthId} TAHUN {$year}");
                $sheet->getStyle('A4')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Row 5: blank spacer (already blank from insert)

                // ── Map collection rows → sheet rows ──────────────────────────
                $colHdrRow    = 1  + $off; // 6  — column header
                $jumlahRow    = $this->jumlahCollRow    + $off;
                $saldoRow     = $this->saldoCollRow     + $off;
                $tabHdrRow    = $this->tabHdrCollRow    + $off;
                $tabColRow    = $this->tabColCollRow    + $off;
                $tabJumlahRow = $this->tabJumlahCollRow + $off;
                $tabSaldoRow  = $this->tabSaldoCollRow  + $off;
                $lastRow      = $sheet->getHighestRow();

                // ── Column header rows (main + tabungan) ──────────────────────
                foreach ([$colHdrRow, $tabColRow] as $r) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1C4532']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // ── JUMLAH rows ───────────────────────────────────────────────
                foreach ([$jumlahRow, $tabJumlahRow] as $r) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFECFDF5']],
                    ]);
                }

                // ── SALDO rows ────────────────────────────────────────────────
                foreach ([$saldoRow, $tabSaldoRow] as $r) {
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']],
                    ]);
                }

                // ── Tabungan section header (merged) ──────────────────────────
                $sheet->mergeCells("A{$tabHdrRow}:{$lastCol}{$tabHdrRow}");
                $sheet->getStyle("A{$tabHdrRow}:{$lastCol}{$tabHdrRow}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension($tabHdrRow)->setRowHeight(18);

                // ── Thin borders for all data rows ────────────────────────────
                $sheet->getStyle("A{$colHdrRow}:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFB0BEC5'],
                        ],
                    ],
                ]);

                // ── Right-align amount columns ────────────────────────────────
                $sheet->getStyle("D{$colHdrRow}:E{$lastRow}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);

                // ── Column widths ─────────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(14);
                $sheet->getColumnDimension('C')->setWidth(50);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(18);

                // ── Title row heights ─────────────────────────────────────────
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(18);

                // ── Freeze pane below column headers ──────────────────────────
                $sheet->freezePane('A' . ($colHdrRow + 1));
            },
        ];
    }

}
