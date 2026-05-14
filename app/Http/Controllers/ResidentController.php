<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\Block;
use App\Models\FeeHistory;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
          ->orderBy('residents.block_id')->orderBy('units.unit_number')
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
