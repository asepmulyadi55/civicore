<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHouseholderRequest;
use App\Http\Requests\UpdateHouseholderRequest;
use App\Models\Block;
use App\Models\FeeHistory;
use App\Models\PaymentRecord;
use App\Models\Householder;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HouseholderController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scopeBlockId = $user->isBlockCoordinator() ? $user->block_id : null;

        $query = Householder::with([
            'block',
            'unit',
            'feeHistories' => function ($q) {
                $q->orderByDesc('effective_from')->limit(1);
            },
            'residents' => fn($q) => $q->where('is_head', true)->select('id', 'householder_id', 'fullname'),
        ])->withCount('residents')
          ->leftJoin('units', 'units.id', '=', 'householders.unit_id')
          ->leftJoin('blocks', 'blocks.id', '=', 'householders.block_id')
          ->select('householders.*');


        // Scope to coordinator's block
        if ($scopeBlockId) {
            $query->where('householders.block_id', $scopeBlockId);
        }

        // Live search — includes resident names
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('householders.fullname', 'like', "%{$search}%")
                    ->orWhere('units.unit_number', 'like', "%{$search}%")
                    ->orWhere('householders.phone', 'like', "%{$search}%")
                    ->orWhereHas('block', fn($b) => $b->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('residents', fn($f) => $f->where('fullname', 'like', "%{$search}%"));
            });
        }

        // Block filter (hidden for coordinators, but harmless if still sent)
        if (!$scopeBlockId && $blockId = $request->get('block_id')) {
            $query->where('householders.block_id', $blockId);
        }

        // Status filter
        if ($request->get('status') === 'active') {
            $query->where('householders.is_active', true);
        } elseif ($request->get('status') === 'inactive') {
            $query->where('householders.is_active', false);
        }

        // Dynamic column sort
        $sortableColumns = [
            'fullname'     => 'householders.fullname',
            'house_status' => 'units.house_status',
            'is_active'    => 'householders.is_active',
        ];
        $sort = $request->get('sort');
        $dir  = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (isset($sortableColumns[$sort])) {
            $query->orderBy($sortableColumns[$sort], $dir);
        } else {
            $query->orderByRaw("
                blocks.name,
                LEFT(units.unit_number, LOCATE('-', units.unit_number) - 1),
                CAST(SUBSTRING(units.unit_number, LOCATE('-', units.unit_number) + 1) AS UNSIGNED),
                units.unit_number
            ");
        }

        $householders = $query->paginate(15)->withQueryString();
        $blocks = Block::active()->orderBy('name')->get();

        $baseCount   = Householder::when($scopeBlockId, fn($q) => $q->where('householders.block_id', $scopeBlockId));
        $totalCount = (clone $baseCount)->count();
        $activeCount = (clone $baseCount)->where('is_active', true)->count();
        $currency = Setting::get('currency_symbol', 'Rp');

        return view('householders', compact('householders', 'blocks', 'totalCount', 'activeCount', 'currency'));
    }

    public function store(StoreHouseholderRequest $request)
    {
        DB::transaction(function () use ($request) {
            $unit = Unit::findOrFail($request->unit_id);

            $householder = Householder::create([
                'block_id'           => $unit->block_id,
                'unit_id'            => $unit->id,
                'fullname'           => $request->fullname,
                'phone'              => null, // Hardened: Ignore submissions
                'email'              => $request->email,
                'family_card_number' => null, // Hardened: Ignore submissions
                'notes'              => $request->notes,
                'is_active'          => true,
            ]);

            // Create the initial fee history entry
            FeeHistory::create([
                'householder_id' => $householder->id,
                'amount' => $request->monthly_fee,
                'effective_from' => Carbon::createFromFormat('Y-m', $request->fee_start)->startOfMonth(),
                'created_by' => auth()->id(),
                'notes' => 'Initial fee assignment',
            ]);

            // Auto-link to a matching user account by email
            $this->linkUserToHouseholder($householder);
        });

        return redirect()->route('householders.index')
            ->with('success', __('app.flash_householder_added'));
    }

    /**
     * Import householders, fee histories, and payment records from an Excel file.
     *
     * Column layout (Data IPL.xlsx sheet, data from row 2):
     *   A = Block letter | B = Unit number | C = Full name | D = Status Warga
     *   E = Monthly fee amount
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'year'       => ['required', 'integer', 'min:2020', 'max:2035'],
        ], [
            'excel_file.required' => 'Please choose an Excel file.',
            'excel_file.mimes'    => 'Only .xlsx and .xls files are accepted.',
        ]);

        $year = (int) $request->input('year', 2026);

        $year = (int) $request->input('year', 2026);

        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
        $sheet       = $spreadsheet->getSheet(0);
        $maxRow      = $sheet->getHighestRow();

        $stats = ['householders_created' => 0, 'householders_skipped' => 0,
                  'fees_created' => 0];

        $effectiveFrom = Carbon::create($year, 1, 1)->toDateString();

        DB::transaction(function () use ($sheet, $maxRow, $effectiveFrom, &$stats) {
            $currentBlock = '';
            for ($row = 2; $row <= $maxRow; $row++) {
                $blockLetter = strtoupper(trim($sheet->getCell('A' . $row)->getCalculatedValue() ?? ''));
                if ($blockLetter !== '') {
                    $currentBlock = $blockLetter;
                } else {
                    $blockLetter = $currentBlock;
                }

                $unitNum     = trim($sheet->getCell('B' . $row)->getCalculatedValue() ?? '');
                $name        = trim($sheet->getCell('C' . $row)->getCalculatedValue() ?? '');
                $rawStatus   = strtolower(trim(preg_replace('/\s+/', ' ',
                    $sheet->getCell('D' . $row)->getCalculatedValue() ?? '')));

                // Skip empty rows, header repeats, common areas, and nameless units
                if (empty($blockLetter) || empty($unitNum) || empty($name)) continue;
                if (in_array($rawStatus, ['fasum', 'fasilitasumum', 'developer'])) continue;
                if (!ctype_alpha($blockLetter)) continue;

                // ── Find Block + Unit (must exist from block import) ───────
                $block = Block::where('name', $blockLetter)->first();
                if (!$block) continue;

                $unit = Unit::where('block_id', $block->id)->where('unit_number', $unitNum)->first();
                if (!$unit) continue;

                // ── Householder (one per unit) ────────────────────────────
                $householder = Householder::firstOrCreate(
                    ['unit_id' => $unit->id],
                    ['fullname' => $name, 'block_id' => $block->id, 'is_active' => true]
                );
                $householder->wasRecentlyCreated
                    ? $stats['householders_created']++
                    : $stats['householders_skipped']++;

                // ── Monthly fee (column E) ─────────────────────────────────
                $feeAmount = (float) ($sheet->getCell('E' . $row)->getCalculatedValue() ?? 0);
                if ($feeAmount > 0) {
                    $feeExists = FeeHistory::where('householder_id', $householder->id)
                        ->where('effective_from', $effectiveFrom)->exists();
                    if (!$feeExists) {
                        FeeHistory::create([
                            'householder_id' => $householder->id,
                            'amount'         => $feeAmount,
                            'effective_from' => $effectiveFrom,
                            'notes'          => "Imported from Excel ({$effectiveFrom})",
                        ]);
                        $stats['fees_created']++;
                    }
                }
            }
        });

        $summary = sprintf(
            'Import complete — %d householder(s) created, %d already existed | %d fee record(s) created.',
            $stats['householders_created'], $stats['householders_skipped'],
            $stats['fees_created']
        );

        return redirect()->route('householders.index')->with('success', $summary);
    }

    public function edit(Householder $householder)
    {
        $householder->load([
            'block',
            'unit',
            'residents',
            'feeHistories' => fn($q) => $q->orderByDesc('effective_from'),
        ]);
        $blocks   = Block::active()->orderBy('name')->get();
        $units    = $householder->block ? $householder->block->units()->active()->orderBy('unit_number')->with('householder:id,unit_id')->get() : collect();
        $currency = Setting::get('currency_symbol', 'Rp');

        $canManageInfo      = true;
        $canManageResidents = true;
        $updateRoute        = route('householders.update', $householder);
        $residentsBase      = url("/householders/{$householder->id}/residents");
        $backRoute          = route('householders.index');
        $showRevealButtons  = auth()->user()->isAdmin();
        $isOwnHousehold     = false;

        return view('householders.edit', compact(
            'householder', 'blocks', 'units', 'currency',
            'canManageInfo', 'canManageResidents',
            'updateRoute', 'residentsBase',
            'backRoute', 'showRevealButtons', 'isOwnHousehold'
        ));
    }

    public function update(UpdateHouseholderRequest $request, Householder $householder)
    {
        DB::transaction(function () use ($request, $householder) {
            $data = $request->only([
                'fullname', 'phone', 'email', 'block_id', 'unit_id', 'is_active',
                'family_card_number', 'notes', 'rent_start', 'rent_end',
            ]);

            // Keep block_id in sync with the selected unit
            if ($request->filled('unit_id')) {
                $unit = Unit::find($request->unit_id);
                if ($unit) {
                    $data['block_id'] = $unit->block_id;
                }
            }

            // Hardened: completely ignore nik, no_kk, phone submissions for privacy
            unset($data['nik'], $data['family_card_number'], $data['phone']);

            // Handle optional photo upload
            if ($request->hasFile('photo')) {
                if ($householder->photo_path) {
                    Storage::disk('local')->delete($householder->photo_path);
                }
                $data['photo_path'] = $request->file('photo')->store('householders', 'local');
            }

            $householder->update($data);

            // Optional: create a new FeeHistory entry if a new fee is provided
            if ($request->filled('new_monthly_fee')) {
                $effectiveFrom = Carbon::createFromFormat('Y-m', $request->new_fee_start ?? now()->format('Y-01'))->startOfMonth();
                FeeHistory::updateOrCreate(
                    [
                        'householder_id' => $householder->id,
                        'effective_from' => $effectiveFrom
                    ],
                    [
                        'amount' => $request->new_monthly_fee,
                        'created_by' => auth()->id(),
                        'notes' => 'Fee updated via householder edit',
                    ]
                );
            }

            // Re-link in case email changed or was just filled in
            $this->linkUserToHouseholder($householder->fresh());
        });

        return redirect()->route('householders.edit', $householder)
            ->with('success', __('app.flash_household_updated'));
    }

    /**
     * Soft-deactivate: marks inactive but preserves all payment history.
     */
    public function deactivate(Householder $householder)
    {
        $householder->update(['is_active' => false]);

        Log::info('Householder deactivated', [
            'householder_id' => $householder->id,
            'name' => $householder->fullname,
            'by' => auth()->id(),
        ]);

        return redirect()->route('householders.index')
            ->with('success', __('app.flash_householder_deactivated', ['name' => $householder->fullname]));
    }

    /**
     * Hard delete: permanently removes householder and unlinks their user account.
     */
    public function destroy(Householder $householder)
    {
        $name = $householder->fullname;

        // Unlink from user account so the user isn't orphaned, then delete
        $householder->update(['user_id' => null]);
        $householder->delete();

        Log::warning('Householder permanently deleted', [
            'householder_id' => $householder->id,
            'name' => $name,
            'block' => $householder->block_id,
            'deleted_by' => auth()->id(),
        ]);

        return redirect()->route('householders.index')
            ->with('success', __('app.flash_householder_deleted', ['name' => $name]));
    }

    /**
     * Link a householder record to a matching User account by email.
     * Sets householder.user_id and syncs user.block_id.
     */
    private function linkUserToHouseholder(Householder $householder): void
    {
        if (!$householder->email) {
            return;
        }

        $user = User::where('email', $householder->email)->first();

        if (!$user) {
            return;
        }

        // Link the householder to the user
        if ($householder->user_id !== $user->id) {
            $householder->update(['user_id' => $user->id]);
        }

        // Unused block_id reference removed
    }

    /**
     * Bulk delete householders.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->route('householders.index')->with('error', __('app.no_items_selected'));
        }

        $householders = Householder::whereIn('id', $ids)->get();
        $deletedCount = 0;

        foreach ($householders as $householder) {
            // Unlink from user account so the user isn't orphaned, then delete
            $householder->update(['user_id' => null]);
            $householder->delete();
            $deletedCount++;
        }

        Log::warning('Householders bulk deleted', [
            'count'      => $deletedCount,
            'deleted_by' => auth()->id(),
        ]);

        return redirect()->route('householders.index')
            ->with('success', __('app.flash_householders_bulk_deleted', ['count' => $deletedCount]));
    }
}
