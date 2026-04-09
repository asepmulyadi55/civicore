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
    $this->year = $year;
    $this->blockId = $blockId;
    $this->currency = Setting::get('currency_symbol', 'Rp');
    $this->monthLabels = [];
    for ($m = 1; $m <= 12; $m++) {
      $this->monthLabels[$m] = Carbon::create($year, $m, 1)->format('M');
    }
  }

  public function title(): string
  {
    return "Report {$this->year}";
  }

  public function headings(): array
  {
    return array_merge(
      ['No', 'Unit', 'Resident Name', 'Block'],
      array_values($this->monthLabels),
      ['Annual Total']
    );
  }

  public function collection(): Collection
  {
    $query = Resident::with([
      'block',
      'unit',
      'paymentRecords' => fn($q) => $q->whereYear('payment_month', $this->year),
    ])->where('is_active', true)
      ->leftJoin('units', 'units.id', '=', 'residents.unit_id')
      ->orderBy('residents.block_id')
      ->orderBy('units.unit_number')
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
          $row[] = match ($status) {
            'approved' => '✓ Paid',
            'pending' => '⏳ Pending',
            'rejected' => '✗ Rejected',
            default => ucfirst($status),
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
        $sheet->setCellValue('A1', "PAYMENT REPORT — {$this->year}");
        $sheet->getStyle('A1')->applyFromArray([
          'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF4F46E5']],
          'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('d M Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
          'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF64748B']],
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

  private function statusValue(mixed $status): string
  {
    if ($status instanceof \App\Enums\PaymentStatus) {
      return $status->value;
    }
    return (string) $status;
  }
}
