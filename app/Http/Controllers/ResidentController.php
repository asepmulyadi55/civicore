<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Block;
use App\Models\FeeHistory;
use App\Models\PaymentRecord;
use App\Models\Resident;
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

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scopeBlockId = $user->isBlockCoordinator() ? $user->block_id : null;

        $query = Resident::with([
            'block',
            'unit',
            'feeHistories' => function ($q) {
                $q->orderByDesc('effective_from')->limit(1);
            },
            'familyMembers' => fn($q) => $q->where('is_head', true)->select('id', 'resident_id', 'fullname'),
        ])->withCount('familyMembers')
          ->leftJoin('units', 'units.id', '=', 'residents.unit_id')
          ->select('residents.*');


        // Scope to coordinator's block
        if ($scopeBlockId) {
            $query->where('residents.block_id', $scopeBlockId);
        }

        // Live search — includes family member names
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('residents.fullname', 'like', "%{$search}%")
                    ->orWhere('units.unit_number', 'like', "%{$search}%")
                    ->orWhere('residents.phone', 'like', "%{$search}%")
                    ->orWhereHas('block', fn($b) => $b->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('familyMembers', fn($f) => $f->where('fullname', 'like', "%{$search}%"));
            });
        }

        // Block filter (hidden for coordinators, but harmless if still sent)
        if (!$scopeBlockId && $blockId = $request->get('block_id')) {
            $query->where('residents.block_id', $blockId);
        }

        // Status filter
        if ($request->get('status') === 'active') {
            $query->where('residents.is_active', true);
        } elseif ($request->get('status') === 'inactive') {
            $query->where('residents.is_active', false);
        }

        // Dynamic column sort
        $sortableColumns = [
            'fullname'     => 'residents.fullname',
            'house_status' => 'units.house_status',
            'is_active'    => 'residents.is_active',
        ];
        $sort = $request->get('sort');
        $dir  = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        if (isset($sortableColumns[$sort])) {
            $query->orderBy($sortableColumns[$sort], $dir);
        } else {
            $query->orderBy('residents.block_id', 'asc')->orderBy('units.unit_number', 'asc');
        }

        $residents = $query->paginate(15)->withQueryString();
        $blocks = Block::active()->orderBy('name')->get();


        $baseCount   = Resident::when($scopeBlockId, fn($q) => $q->where('residents.block_id', $scopeBlockId));
        $totalCount = (clone $baseCount)->count();
        $activeCount = (clone $baseCount)->where('is_active', true)->count();
        $currency = Setting::get('currency_symbol', 'Rp');

        return view('residents', compact('residents', 'blocks', 'totalCount', 'activeCount', 'currency'));
    }

    public function store(StoreResidentRequest $request)
    {
        DB::transaction(function () use ($request) {
            $unit = Unit::findOrFail($request->unit_id);

            $resident = Resident::create([
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
                'resident_id' => $resident->id,
                'amount' => $request->monthly_fee,
                'effective_from' => Carbon::createFromFormat('Y-m', $request->fee_start)->startOfMonth(),
                'created_by' => auth()->id(),
                'notes' => 'Initial fee assignment',
            ]);

            // Auto-link to a matching user account by email
            $this->linkUserToResident($resident);
        });

        return redirect()->route('residents.index')
            ->with('success', 'Resident added successfully.');
    }

    /**
     * Import residents, fee histories, and payment records from an Excel file.
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

        $stats = ['residents_created' => 0, 'residents_skipped' => 0,
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

                // ── Resident (one per unit) ────────────────────────────────
                $resident = Resident::firstOrCreate(
                    ['unit_id' => $unit->id],
                    ['fullname' => $name, 'block_id' => $block->id, 'is_active' => true]
                );
                $resident->wasRecentlyCreated
                    ? $stats['residents_created']++
                    : $stats['residents_skipped']++;

                // ── Monthly fee (column K) ─────────────────────────────────
                $feeAmount = (float) ($sheet->getCell('K' . $row)->getCalculatedValue() ?? 0);
                if ($feeAmount > 0) {
                    $feeExists = FeeHistory::where('resident_id', $resident->id)
                        ->where('effective_from', $effectiveFrom)->exists();
                    if (!$feeExists) {
                        FeeHistory::create([
                            'resident_id'    => $resident->id,
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
                    $exists = PaymentRecord::where('resident_id', $resident->id)
                        ->where('payment_month', $paymentMonth)->exists();

                    if ($exists) { $stats['payments_skipped']++; continue; }

                    PaymentRecord::create([
                        'resident_id'   => $resident->id,
                        'payment_month' => $paymentMonth,
                        'amount'        => $feeAmount > 0 ? $feeAmount : 165000,
                        'status'        => PaymentStatus::Approved,
                        'notes'         => 'Imported from Excel',
                    ]);
                    $stats['payments_created']++;
                }
            }
        });

        $summary = sprintf(
            'Import complete — %d resident(s) created, %d already existed | %d fee record(s) created | %d payment(s) imported, %d already existed.',
            $stats['residents_created'], $stats['residents_skipped'],
            $stats['fees_created'],
            $stats['payments_created'], $stats['payments_skipped']
        );

        return redirect()->route('residents.index')->with('success', $summary);
    }

    public function edit(Resident $resident)
    {
        $resident->load([
            'block',
            'unit',
            'familyMembers',
            'feeHistories' => fn($q) => $q->orderByDesc('effective_from'),
        ]);
        $blocks   = Block::active()->orderBy('name')->get();
        $units    = $resident->block ? $resident->block->units()->active()->orderBy('unit_number')->with('resident:id,unit_id')->get() : collect();
        $currency = Setting::get('currency_symbol', 'Rp');

        $canManageInfo        = true;
        $canManageFamilyMembers = true;
        $updateRoute          = route('residents.update', $resident);
        $familyMembersBase    = url("/residents/{$resident->id}/family-members");
        $backRoute            = route('residents.index');
        $showRevealButtons    = auth()->user()->isAdmin();
        $isOwnHousehold       = false;

        return view('residents.edit', compact(
            'resident', 'blocks', 'units', 'currency',
            'canManageInfo', 'canManageFamilyMembers',
            'updateRoute', 'familyMembersBase',
            'backRoute', 'showRevealButtons', 'isOwnHousehold'
        ));
    }

    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        DB::transaction(function () use ($request, $resident) {
            $data = $request->only([
                'fullname', 'phone', 'email', 'block_id', 'unit_id', 'is_active',
                'family_card_number', 'notes',
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
                if ($resident->photo_path) {
                    Storage::disk('local')->delete($resident->photo_path);
                }
                $data['photo_path'] = $request->file('photo')->store('residents', 'local');
            }

            $resident->update($data);

            // Optional: create a new FeeHistory entry if a new fee is provided
            if ($request->filled('new_monthly_fee')) {
                FeeHistory::create([
                    'resident_id' => $resident->id,
                    'amount' => $request->new_monthly_fee,
                    'effective_from' => Carbon::createFromFormat('Y-m', $request->new_fee_start ?? now()->format('Y-m'))->startOfMonth(),
                    'created_by' => auth()->id(),
                    'notes' => 'Fee updated via resident edit',
                ]);
            }

            // Re-link in case email changed or was just filled in
            $this->linkUserToResident($resident->fresh());
        });

        return redirect()->route('residents.edit', $resident)
            ->with('success', 'Household updated successfully.');
    }

    /**
     * Soft-deactivate: marks inactive but preserves all payment history.
     */
    public function deactivate(Resident $resident)
    {
        $resident->update(['is_active' => false]);

        Log::info('Resident deactivated', [
            'resident_id' => $resident->id,
            'name' => $resident->fullname,
            'by' => auth()->id(),
        ]);

        return redirect()->route('residents.index')
            ->with('success', "{$resident->fullname} has been deactivated.");
    }

    /**
     * Hard delete: permanently removes resident and unlinks their user account.
     */
    public function destroy(Resident $resident)
    {
        $name = $resident->fullname;

        // Unlink from user account so the user isn't orphaned, then delete
        User::where('email', $resident->email)->update(['block_id' => null]);
        $resident->update(['user_id' => null]);
        $resident->delete();

        Log::warning('Resident permanently deleted', [
            'resident_id' => $resident->id,
            'name' => $name,
            'block' => $resident->block_id,
            'deleted_by' => auth()->id(),
        ]);

        return redirect()->route('residents.index')
            ->with('success', "{$name} has been permanently deleted.");
    }

    /**
     * Link a resident record to a matching User account by email.
     * Sets resident.user_id and syncs user.block_id.
     */
    private function linkUserToResident(Resident $resident): void
    {
        if (!$resident->email) {
            return;
        }

        $user = User::where('email', $resident->email)->first();

        if (!$user) {
            return;
        }

        // Link the resident to the user
        if ($resident->user_id !== $user->id) {
            $resident->update(['user_id' => $user->id]);
        }

        // Sync the user's block_id from the resident's block
        if ($resident->block_id && $user->block_id !== $resident->block_id) {
            $user->update(['block_id' => $resident->block_id]);
        }
    }
}
