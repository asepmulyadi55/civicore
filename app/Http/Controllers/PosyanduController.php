<?php

namespace App\Http\Controllers;

use App\Exports\PosyanduExport;
use App\Models\Block;
use App\Models\FamilyMember;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PosyanduController extends Controller
{
    public const CATEGORIES = [
        'baby'    => ['label' => 'Bayi',           'desc' => '0-11 bln',            'icon' => 'child_care',        'color' => 'pink'],
        'toddler' => ['label' => 'Balita',          'desc' => '1-4 thn',             'icon' => 'escalator_warning', 'color' => 'purple'],
        'child'   => ['label' => 'Anak',            'desc' => '5-11 thn',            'icon' => 'face',              'color' => 'blue'],
        'teen'    => ['label' => 'Remaja',          'desc' => '12-17 thn',           'icon' => 'school',            'color' => 'indigo'],
        'adult'   => ['label' => 'Dewasa',          'desc' => '18-59 thn',           'icon' => 'person',            'color' => 'emerald'],
        'elderly' => ['label' => 'Lansia',          'desc' => '60+ thn',             'icon' => 'elderly',           'color' => 'amber'],
        'unknown' => ['label' => 'Tidak Diketahui', 'desc' => 'Tgl lahir tidak ada', 'icon' => 'help_outline',      'color' => 'slate'],
    ];

    public const DEFAULT_LIMITS = [
        'baby_max_months'    => 12,
        'toddler_max_months' => 60,
        'child_max_months'   => 144,
        'teen_max_months'    => 216,
        'adult_max_months'   => 720,
    ];

    public static function categoryLimits(): array
    {
        $limits = [];
        foreach (self::DEFAULT_LIMITS as $key => $default) {
            $limits[$key] = (int) Setting::get('posyandu_' . $key, (string) $default);
        }
        return $limits;
    }

    public static function translatedCategories(): array
    {
        $cats = self::CATEGORIES;
        foreach ($cats as $key => &$cat) {
            $cat['label'] = __('app.posyandu_cat_' . $key . '_label');
            $cat['desc']  = __('app.posyandu_cat_' . $key . '_desc');
        }
        return $cats;
    }

    public function index(Request $request)
    {
        $query = FamilyMember::with(['resident.block', 'resident.unit'])->orderBy('fullname');

        if ($blockId = $request->input('block_id')) {
            $query->whereHas('resident', fn($q) => $q->where('block_id', $blockId));
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhereHas('resident', fn($r) => $r->where('fullname', 'like', "%{$search}%"));
            });
        }

        $all    = $query->get();
        $limits = self::categoryLimits();

        $all->each(function (FamilyMember $m) use ($limits) {
            $m->age_category = $this->resolveCategory($m->birth_date, $limits);
            $m->age_label    = $m->birth_date ? $this->ageLabel($m->birth_date) : '---';
        });

        // Gender stats from full unfiltered set
        $genderStats = [
            'male'    => $all->where('gender', 'male')->count(),
            'female'  => $all->where('gender', 'female')->count(),
            'unknown' => $all->whereNotIn('gender', ['male', 'female'])->count(),
            'total'   => $all->count(),
        ];

        // Category filter
        $categoryFilter = $request->input('category');
        $filtered = ($categoryFilter && array_key_exists($categoryFilter, self::CATEGORIES))
            ? $all->filter(fn($m) => $m->age_category === $categoryFilter)->values()
            : $all;

        // Gender filter
        $genderFilter = $request->input('gender');
        if ($genderFilter && in_array($genderFilter, ['male', 'female'])) {
            $filtered = $filtered->filter(fn($m) => $m->gender === $genderFilter)->values();
        }

        $categoryCounts = collect(self::CATEGORIES)->mapWithKeys(function ($_, $key) use ($all) {
            return [$key => $all->filter(fn($m) => $m->age_category === $key)->count()];
        })->all();

        $perPage     = 20;
        $currentPage = max(1, (int) $request->input('page', 1));
        $total       = $filtered->count();
        $items       = $filtered->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $members = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, $total, $perPage, $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $blocks = Block::active()->orderBy('name')->get();

        return view('posyandu', compact(
            'members', 'blocks', 'categoryCounts', 'categoryFilter', 'genderFilter', 'genderStats'
        ));
    }

    public function export(Request $request)
    {
        $blockId  = $request->input('block_id') ? (int) $request->input('block_id') : null;
        $category = $request->input('category') ?: null;
        $gender   = $request->input('gender') ?: null;
        $search   = $request->input('search') ?: null;

        $filename = 'posyandu_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new PosyanduExport($blockId, $category, $gender, $search),
            $filename
        );
    }

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