<?php

namespace App\Exports;

use App\Http\Controllers\PosyanduController;
use App\Models\Block;
use App\Models\Resident;
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

class PosyanduExport implements
    FromCollection,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithEvents
{
    protected ?int    $blockId;
    protected ?string $category;
    protected ?string $gender;
    protected ?string $search;
    protected string  $locale;
    protected array   $translatedCats;

    public function __construct(?int $blockId, ?string $category, ?string $gender, ?string $search)
    {
        $this->blockId  = $blockId;
        $this->category = $category;
        $this->gender   = $gender;
        $this->search   = $search;

        $this->locale = session('app_locale', config('app.locale', 'en'));
        app()->setLocale($this->locale);

        $this->translatedCats = PosyanduController::translatedCategories();
    }

    public function collection(): Collection
    {
        $query = Resident::with(['householder.block', 'householder.unit'])
            ->orderBy('fullname');

        if ($this->blockId) {
            $query->whereHas('householder', fn($q) => $q->where('block_id', $this->blockId));
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('fullname', 'like', "%{$this->search}%")
                  ->orWhereHas('householder', fn($r) => $r->where('fullname', 'like', "%{$this->search}%"));
            });
        }

        if ($this->gender) {
            $query->where('gender', $this->gender);
        }

        $all    = $query->get();
        $limits = PosyanduController::categoryLimits();

        $all->each(function (FamilyMember $m) use ($limits) {
            $m->age_category = $this->resolveCategory($m->birth_date, $limits);
            $m->age_label    = $m->birth_date ? $this->ageLabel($m->birth_date) : '—';
        });

        if ($this->category && array_key_exists($this->category, PosyanduController::CATEGORIES)) {
            $all = $all->filter(fn($m) => $m->age_category === $this->category)->values();
        }

        $isId = $this->locale === 'id';

        return $all->map(function (FamilyMember $m) use ($isId) {
            $cat      = $this->translatedCats[$m->age_category] ?? $this->translatedCats['unknown'];
            $resident = $m->householder;

            $gender = match($m->gender) {
                'male'   => $isId ? 'Laki-laki' : 'Male',
                'female' => $isId ? 'Perempuan'  : 'Female',
                default  => '—',
            };

            $relKey   = 'rel_' . ($m->relationship ?? 'other');
            $relLabel = __('app.' . $relKey);
            if ($relLabel === 'app.' . $relKey) {
                $relLabel = \App\Models\Resident::$relationships[$m->relationship] ?? 'Other';
            }

            return [
                $m->fullname,
                $m->birth_date?->format('d/m/Y') ?? '—',
                $m->age_label,
                $cat['label'] . ' (' . $cat['desc'] . ')',
                $gender,
                $relLabel,
                $resident?->block?->name ?? '—',
                $resident?->unit_number ?? '—',
                $resident?->displayName() ?? '—',
            ];
        });
    }

    public function headings(): array
    {
        return [
            __('app.posyandu_col_name'),
            __('app.posyandu_col_dob'),
            __('app.posyandu_col_age'),
            __('app.posyandu_col_cat'),
            __('app.posyandu_col_gender'),
            __('app.posyandu_col_rel'),
            __('app.table_block'),
            __('app.unit_no'),
            __('app.posyandu_col_household'),
        ];
    }

    public function title(): string
    {
        return 'Posyandu';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Heading row
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0D9488']], // teal-600
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $sheet->getHighestRow();
                $lastCol   = $sheet->getHighestColumn();
                $dataRange = "A1:{$lastCol}{$lastRow}";

                // Borders on all cells
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFE2E8F0'],
                        ],
                    ],
                ]);

                // Zebra-stripe data rows
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FDFA']],
                        ]);
                    }
                }

                // Freeze header row
                $sheet->freezePane('A2');

                // Metadata rows above the heading
                $sheet->insertNewRowBefore(1, 3);

                $communityName = Setting::get('community_name', 'Posyandu');
                $blockLabel    = $this->blockId
                    ? (Block::find($this->blockId)?->name ?? "Block #{$this->blockId}")
                    : ($this->locale === 'id' ? 'Semua Blok' : 'All Blocks');

                $catLabel = $this->category
                    ? ($this->translatedCats[$this->category]['label'] ?? ucfirst($this->category))
                    : ($this->locale === 'id' ? 'Semua Kategori' : 'All Categories');

                $genLabel = $this->gender
                    ? ($this->locale === 'id'
                        ? ($this->gender === 'male' ? 'Laki-laki' : 'Perempuan')
                        : ucfirst($this->gender))
                    : ($this->locale === 'id' ? 'Semua' : 'All');

                $title     = $this->locale === 'id' ? 'Data Posyandu' : 'Posyandu Data';
                $generated = $this->locale === 'id' ? 'Diekspor' : 'Exported';
                $blockLbl  = $this->locale === 'id' ? 'Blok' : 'Block';
                $catLbl    = $this->locale === 'id' ? 'Kategori' : 'Category';
                $genLbl    = $this->locale === 'id' ? 'Jenis Kelamin' : 'Gender';

                $sheet->setCellValue('A1', $communityName . ' — ' . $title);
                $sheet->setCellValue('A2', "{$blockLbl}: {$blockLabel}  |  {$catLbl}: {$catLabel}  |  {$genLbl}: {$genLabel}");
                $sheet->setCellValue('A3', "{$generated}: " . now()->format('d/m/Y H:i'));

                // Style metadata rows
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF0D9488']],
                ]);
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['argb' => 'FF64748B']],
                ]);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF94A3B8']],
                ]);

                // Freeze below metadata + heading
                $sheet->freezePane('A5');

                // Row height for header (now row 4)
                $sheet->getRowDimension(4)->setRowHeight(28);
            },
        ];
    }

    // ── Helpers (copied from PosyanduController) ─────────────────────────────

    private function resolveCategory(?Carbon $birthDate, array $limits): string
    {
        if (!$birthDate) return 'unknown';
        $months = (int) $birthDate->diffInMonths(now());
        return match(true) {
            $months < $limits['baby_max_months']    => 'baby',
            $months < $limits['toddler_max_months'] => 'toddler',
            $months < $limits['child_max_months']   => 'child',
            $months < $limits['teen_max_months']    => 'teen',
            $months < $limits['adult_max_months']   => 'adult',
            default                                 => 'elderly',
        };
    }

    private function ageLabel(Carbon $birthDate): string
    {
        $now    = now();
        $years  = (int) $birthDate->diffInYears($now);
        $months = (int) $birthDate->copy()->addYears($years)->diffInMonths($now);
        if ($years === 0) return $months . ' bln';
        if ($months === 0) return $years . ' thn';
        return $years . ' thn ' . $months . ' bln';
    }
}



