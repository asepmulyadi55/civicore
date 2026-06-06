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
                'phone'              => $request->phone,
                'email'              => $request->email,
                'family_card_number' => $request->family_card_number,
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
     * Column layout (IuranWarga sheet, data from row 4):
     *   F  = Block letter | G  = Unit number | I  = Full name | J  = Status Warga
     *   K  = Monthly fee amount
     *   Per month (3 cols each): fee, payment date, status ('L'=Lunas/'BL'=Belum Lunas)
     *   Jan→K/L/M  Feb→N/O/P  Mar→Q/R/S  Apr→T/U/V  May→W/X/Y  Jun→Z/AA/AB
     *   Jul→AC/AD/AE  Aug→AF/AG/AH  Sep→AI/AJ/AK  Oct→AL/AM/AN  Nov→AO/AP/AQ  Dec→AR/AS/AT
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

        // Month column map: month_number => [fee_col, date_col, status_col]
        $monthCols = [
            1  => ['K', 'L', 'M'],   2  => ['N', 'O', 'P'],
            3  => ['Q', 'R', 'S'],   4  => ['T', 'U', 'V'],
            5  => ['W', 'X', 'Y'],   6  => ['Z', 'AA', 'AB'],
            7  => ['AC', 'AD', 'AE'], 8 => ['AF', 'AG', 'AH'],
            9  => ['AI', 'AJ', 'AK'], 10 => ['AL', 'AM', 'AN'],
            11 => ['AO', 'AP', 'AQ'], 12 => ['AR', 'AS', 'AT'],
        ];

        $spreadsheet = IOFactory::load($request->file('excel_file')->getRealPath());
        $sheet       = $spreadsheet->getSheet(0);
        $maxRow      = $sheet->getHighestRow();

        $stats = ['householders_created' => 0, 'householders_skipped' => 0,
                  'fees_created' => 0, 'payments_created' => 0, 'payments_skipped' => 0];

        $effectiveFrom = Carbon::create($year, 1, 1)->toDateString();

        DB::transaction(function () use ($sheet, $maxRow, $year, $monthCols, $effectiveFrom, &$stats) {
            for ($row = 4; $row <= $maxRow; $row++) {
                $blockLetter = strtoupper(trim($sheet->getCell('F' . $row)->getCalculatedValue() ?? ''));
                $unitNum     = trim($sheet->getCell('G' . $row)->getCalculatedValue() ?? '');
                $name        = trim($sheet->getCell('I' . $row)->getCalculatedValue() ?? '');
                $rawStatus   = strtolower(trim(preg_replace('/\s+/', ' ',
                    $sheet->getCell('J' . $row)->getCalculatedValue() ?? '')));

                // Skip empty rows, header repeats, common areas, and nameless units
                if (empty($blockLetter) || empty($unitNum) || empty($name)) continue;
                if (in_array($rawStatus, ['fasum', 'fasilitasumum'])) continue;
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

                // ── Monthly fee (column K) ─────────────────────────────────
                $feeAmount = (float) ($sheet->getCell('K' . $row)->getCalculatedValue() ?? 0);
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

                // ── Payment records for each paid month ────────────────────
                foreach ($monthCols as $month => [$feeCol, $dateCol, $statusCol]) {
                    $monthStatus = strtolower(trim(
                        $sheet->getCell($statusCol . $row)->getCalculatedValue() ?? ''
                    ));
                    if ($monthStatus !== 'l') continue;  // Only import "Lunas"

                    $paymentMonth = Carbon::create($year, $month, 1)->toDateString();
                    $exists = PaymentRecord::where('householder_id', $householder->id)
                        ->where('payment_month', $paymentMonth)->exists();

                    if ($exists) { $stats['payments_skipped']++; continue; }

                    PaymentRecord::create([
                        'householder_id' => $householder->id,
                        'payment_month'  => $paymentMonth,
                        'amount'         => $feeAmount > 0 ? $feeAmount : 165000,
                        'status'         => PaymentStatus::Approved,
                        'notes'          => 'Imported from Excel',
                    ]);
                    $stats['payments_created']++;
                }
            }
        });

        $summary = sprintf(
            'Import complete — %d householder(s) created, %d already existed | %d fee record(s) created | %d payment(s) imported, %d already existed.',
            $stats['householders_created'], $stats['householders_skipped'],
            $stats['fees_created'],
            $stats['payments_created'], $stats['payments_skipped']
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

            // Don't clobber an existing encrypted Family Card Number if the user left the field blank.
            if (!$request->filled('family_card_number')) {
                unset($data['family_card_number']);
            }

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
                FeeHistory::create([
                    'householder_id' => $householder->id,
                    'amount' => $request->new_monthly_fee,
                    'effective_from' => Carbon::createFromFormat('Y-m', $request->new_fee_start ?? now()->format('Y-m'))->startOfMonth(),
                    'created_by' => auth()->id(),
                    'notes' => 'Fee updated via householder edit',
                ]);
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
        User::where('email', $householder->email)->update(['block_id' => null]);
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

        // Sync the user's block_id from the householder's block
        if ($householder->block_id && $user->block_id !== $householder->block_id) {
            $user->update(['block_id' => $householder->block_id]);
        }
    }
}
