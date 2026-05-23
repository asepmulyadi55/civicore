<?php

namespace App\Exports;

use App\Models\FinanceReport;
use App\Models\FinanceTransaction;
use App\Models\PaymentRecord;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinanceReportExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    protected FinanceReport $report;
    protected string $currency;
    protected string $locale;
    protected bool $isId;

    public function __construct(FinanceReport $report)
    {
        $this->report   = $report;
        $this->currency = Setting::get('currency_symbol', 'Rp');
        $this->locale   = session('app_locale', config('app.locale', 'en'));
        $this->isId     = $this->locale === 'id';
    }

    public function title(): string
    {
        $label = $this->isId ? 'Laporan Keuangan' : 'Finance Report';
        return $label . ' ' . Carbon::create($this->report->year, $this->report->month, 1)->format('M Y');
    }

    public function headings(): array
    {
        return $this->isId
            ? ['#', 'Tanggal', 'Deskripsi', 'Kategori', 'Jenis', 'Jumlah']
            : ['#', 'Date', 'Description', 'Category', 'Type', 'Amount'];
    }

    public function collection(): Collection
    {
        $rows = collect();
        $no   = 1;
        $fmt  = fn($n) => $this->currency . ' ' . number_format((float)$n, 0, ',', '.');

        // ── Summary rows ──────────────────────────────────────────────────────
        $summaryLabel = $this->isId ? '— Ringkasan —' : '— Summary —';
        $rows->push(['', $summaryLabel, '', '', '', '']);
        $rows->push(['', $this->isId ? 'Periode' : 'Period',
            Carbon::create($this->report->year, $this->report->month, 1)->format('F Y'), '', '', '']);
        $rows->push(['', $this->isId ? 'Saldo Awal' : 'Opening Balance',
            '', '', '', $fmt($this->report->opening_balance)]);
        $rows->push(['', $this->isId ? 'Total Pemasukan' : 'Total Income',
            '', '', '', $fmt($this->report->total_income)]);
        $rows->push(['', $this->isId ? 'Total Pengeluaran' : 'Total Expense',
            '', '', '', $fmt($this->report->total_expense)]);
        $rows->push(['', $this->isId ? 'Saldo Akhir' : 'Closing Balance',
            '', '', '', $fmt($this->report->closing_balance)]);
        $rows->push(['', '', '', '', '', '']); // blank separator

        // ── Payment income (from PaymentRecord) ───────────────────────────────
        $pyLabel = $this->isId ? '— Pemasukan Warga —' : '— Resident Payments (Income) —';
        $rows->push(['', $pyLabel, '', '', '', '']);

        $payments = PaymentRecord::with('resident')
            ->where('status', 'approved')
            ->whereYear('payment_month', $this->report->year)
            ->whereMonth('payment_month', $this->report->month)
            ->orderBy('payment_month')
            ->get();

        foreach ($payments as $payment) {
            $rows->push([
                $no++,
                Carbon::parse($payment->payment_month)->format('d M Y'),
                $payment->resident?->fullname ?? '—',
                $this->isId ? 'Iuran Warga' : 'Resident Fee',
                $this->isId ? 'Pemasukan' : 'Income',
                $fmt($payment->amount),
            ]);
        }

        if ($payments->isEmpty()) {
            $rows->push(['', $this->isId ? 'Tidak ada' : 'None', '', '', '', '']);
        }

        $rows->push(['', '', '', '', '', '']); // blank separator

        // ── Manual transactions ───────────────────────────────────────────────
        $txLabel = $this->isId ? '— Transaksi Manual —' : '— Manual Transactions —';
        $rows->push(['', $txLabel, '', '', '', '']);

        $transactions = FinanceTransaction::where('report_month', $this->report->month)
            ->where('report_year', $this->report->year)
            ->orderBy('transaction_date')
            ->get();

        foreach ($transactions as $tx) {
            $rows->push([
                $no++,
                $tx->transaction_date->format('d M Y'),
                $tx->description,
                $tx->category ?? '—',
                $tx->type === 'income'
                    ? ($this->isId ? 'Pemasukan' : 'Income')
                    : ($this->isId ? 'Pengeluaran' : 'Expense'),
                ($tx->type === 'expense' ? '-' : '') . $fmt($tx->amount),
            ]);
        }

        if ($transactions->isEmpty()) {
            $rows->push(['', $this->isId ? 'Tidak ada' : 'None', '', '', '', '']);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1C2D27']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'F';

                // Insert title rows at the top
                $sheet->insertNewRowBefore(1, 3);

                $sheet->mergeCells("A1:{$lastCol}1");
                $title = $this->isId ? 'LAPORAN KEUANGAN' : 'FINANCE REPORT';
                $period = Carbon::create($this->report->year, $this->report->month, 1)->format('F Y');
                $sheet->setCellValue('A1', strtoupper($title . ' — ' . $period));
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1C2D27']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $genLabel = $this->isId ? 'Status: ' : 'Status: ';
                $statusMap = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'revised' => 'Revised'];
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', $genLabel . ($statusMap[$this->report->status] ?? $this->report->status));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $genLabel2 = $this->isId ? 'Dibuat: ' : 'Generated: ';
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', $genLabel2 . now()->format('d M Y H:i'));
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Freeze header row
                $sheet->freezePane('A5');

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(16);
                $sheet->getColumnDimension('C')->setWidth(36);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(14);
                $sheet->getColumnDimension('F')->setWidth(20);

                // Borders
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle("A4:{$lastCol}{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFE2E8F0'],
                        ],
                    ],
                ]);

                // Alternate row shading
                for ($row = 5; $row <= $lastRow; $row += 2) {
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF8FAFC'],
                        ],
                    ]);
                }
            },
        ];
    }
}
