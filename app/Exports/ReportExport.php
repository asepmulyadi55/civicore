<?php

namespace App\Exports;

use App\Models\PaymentRecord;
use App\Models\Resident;
use App\Models\Setting;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class ReportExport implements
  FromCollection,
  WithHeadings,
  WithTitle,
  ShouldAutoSize,
  WithStyles,
  WithEvents
{
  protected int $year;
  protected ?int $blockId;
  protected string $currency;
  /** @var array<int, string> Month labels Jan–Dec */
  protected array $monthLabels;

  public function __construct(int $year, ?int $blockId)
  {
    $this->year     = $year;
    $this->blockId  = $blockId;
    $this->currency = Setting::get('currency_symbol', 'Rp');

    // Detect active language from session or default app locale
    $locale = session('app_locale', config('app.locale', 'en'));
    app()->setLocale($locale);

    // Build month labels in the active locale
    $this->monthLabels = [];
    for ($m = 1; $m <= 12; $m++) {
      $carbon = Carbon::create($year, $m, 1);
      // Use translated month names when Indonesian
      $this->monthLabels[$m] = $locale === 'id'
        ? $this->idMonth($m)
        : $carbon->format('M');
    }
  }

  public function title(): string
  {
    $locale = session('app_locale', config('app.locale', 'en'));
    $label  = $locale === 'id' ? 'Laporan Pembayaran' : 'Payment Report';
    return "{$label} {$this->year}";
  }

  public function headings(): array
  {
    $locale = session('app_locale', config('app.locale', 'en'));
    $isId   = $locale === 'id';
    return array_merge(
      [
        'No',
        $isId ? 'Unit'          : 'Unit',
        $isId ? 'Nama Penghuni' : 'Resident Name',
        $isId ? 'Blok'         : 'Block',
      ],
      array_values($this->monthLabels),
      [$isId ? 'Total Tahunan' : 'Annual Total']
    );
  }

  public function collection(): Collection
  {
    $query = Resident::with([
      'block',
      'unit',
      'paymentRecords' => fn($q) => $q->whereYear('payment_month', $this->year),
    ])->where('residents.is_active', true)
      ->leftJoin('units', 'units.id', '=', 'residents.unit_id')
      ->leftJoin('blocks', 'blocks.id', '=', 'residents.block_id')
      ->orderBy('blocks.name')
      ->orderByRaw('CAST(units.unit_number AS UNSIGNED)')
      ->select('residents.*');

    if ($this->blockId) {
      $query->where('residents.block_id', $this->blockId);
    }

    $rows = collect();
    $no = 1;

    foreach ($query->get() as $resident) {
      // Key payment records by month number
      $byMonth = $resident->paymentRecords->keyBy(
        fn($r) => Carbon::parse($r->payment_month)->month
      );

      $annualTotal = $resident->paymentRecords
        ->filter(fn($r) => $this->statusValue($r->status) === 'approved')
        ->sum('amount');

      $row = [
        $no++,
        $resident->unit_number,
        $resident->fullname,
        $resident->block?->name ?? '—',
      ];

      for ($m = 1; $m <= 12; $m++) {
        $record = $byMonth->get($m);
        if (!$record) {
          $row[] = '—';
        } else {
          $status = $this->statusValue($record->status);
          $locale = session('app_locale', config('app.locale', 'en'));
          $isId   = $locale === 'id';
          $row[] = match ($status) {
            'approved' => $isId ? '✓ Lunas'    : '✓ Paid',
            'pending'  => $isId ? '⏳ Menunggu' : '⏳ Pending',
            'rejected' => $isId ? '✗ Ditolak'  : '✗ Rejected',
            default    => ucfirst($status),
          };
        }
      }

      $row[] = $annualTotal > 0
        ? $this->currency . ' ' . number_format($annualTotal, 0, ',', '.')
        : '—';

      $rows->push($row);
    }

    return $rows;
  }

  public function styles(Worksheet $sheet): array
  {
    // Header row style (row 1)
    return [
      1 => [
        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
      ],
    ];
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet->getDelegate();

        // Title row at the very top
        $lastCol = 'Q'; // A-D + 12 months + total = 17 cols = Q
        $sheet->insertNewRowBefore(1, 2);

        $sheet->mergeCells("A1:{$lastCol}1");
        $locale = session('app_locale', config('app.locale', 'en'));
        $isId   = $locale === 'id';
        $label  = $isId ? 'LAPORAN PEMBAYARAN' : 'PAYMENT REPORT';
        $sheet->setCellValue('A1', "{$label} — {$this->year}");
        $sheet->getStyle('A1')->applyFromArray([
          'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF4F46E5']],
          'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $genLabel = $isId ? 'Dibuat: ' : 'Generated: ';
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $genLabel . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
          'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']],
          'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Freeze the header row
        $sheet->freezePane('A4');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);  // No
        $sheet->getColumnDimension('B')->setWidth(10); // Unit
        $sheet->getColumnDimension('C')->setWidth(28); // Name
        $sheet->getColumnDimension('D')->setWidth(12); // Block
  
        // Borders on all data rows
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle("A3:{$lastCol}{$lastRow}")->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
              'color' => ['argb' => 'FFE2E8F0'],
            ],
          ],
        ]);

        // Alternate row shading
        for ($row = 4; $row <= $lastRow; $row += 2) {
          $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
            'fill' => [
              'fillType' => Fill::FILL_SOLID,
              'startColor' => ['argb' => 'FFF8FAFC'],
            ],
          ]);
        }
      },
    ];
  }

  /** Indonesian short month names. */
  private function idMonth(int $m): string
  {
    return ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$m - 1];
  }

  private function statusValue(mixed $status): string
  {
    if ($status instanceof \App\Enums\PaymentStatus) {
      return $status->value;
    }
    return (string) $status;
  }
}
