<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHouseholdRequest;
use App\Models\Block;
use App\Models\Householder;
use App\Models\Resident;
use App\Models\Setting;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HouseholdController extends Controller
{
    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve the authenticated user's linked householder, or abort 403.
     */
    private function getOwnHouseholder(): Householder
    {
        $householder = auth()->user()->resolveHouseholder();

        if (!$householder) {
            abort(403, 'No household record is linked to your account. Please contact your administrator.');
        }

        return $householder;
    }

    /**
     * Ensure the given Resident belongs to the linked householder, or abort 403.
     */
    private function authorizeOwnResident(Householder $householder, Resident $resident): void
    {
        if ($resident->householder_id !== $householder->id) {
            abort(403, 'This resident does not belong to your household.');
        }
    }

    /**
     * Shared validation rules for residents.
     */
    private function residentRules(): array
    {
        return [
            'fullname'     => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'in:head,spouse,child,parent,tenant,other'],
            'nik'          => ['nullable', 'string', 'max:20'],
            'birth_date'   => ['nullable', 'date', 'before_or_equal:today'],
            'gender'       => ['nullable', 'in:male,female'],
            'education'    => ['nullable', 'in:none,elementary,junior_high,senior_high,associate,bachelor,master,doctorate,other'],
            'occupation'   => ['nullable', 'string', 'max:100'],
            'photo'        => ['nullable', 'image', 'max:5120'],
        ];
    }

    private function handleResidentPhoto(Request $request, Resident $resident = null): ?string
    {
        if (!$request->hasFile('photo')) return null;
        if ($resident?->photo_path) {
            Storage::disk('local')->delete($resident->photo_path);
        }
        return $request->file('photo')->store('residents', 'local');
    }

    // -------------------------------------------------------------------------
    // Household info
    // -------------------------------------------------------------------------

    public function show()
    {
        $user        = auth()->user();
        $householder = $this->getOwnHouseholder();

        $householder->load([
            'block',
            'unit',
            'residents',
            'feeHistories' => fn($q) => $q->orderByDesc('effective_from'),
        ]);

        $canManageInfo      = $householder->unit?->house_status === 'owner_occupied';
        $canManageResidents = true;

        $blocks            = Block::active()->orderBy('name')->get();
        $units             = collect(); // householders cannot change their unit
        $currency          = Setting::get('currency_symbol', 'Rp');
        $updateRoute       = route('household.update');
        $residentsBase     = url('/household/residents');
        $backRoute         = route('overview');
        $showRevealButtons = false;
        $isOwnHousehold    = true;

        return view('householders.edit', compact(
            'householder', 'blocks', 'units', 'currency',
            'canManageInfo', 'canManageResidents',
            'updateRoute', 'residentsBase',
            'backRoute', 'showRevealButtons', 'isOwnHousehold'
        ));
    }

    public function update(UpdateHouseholdRequest $request)
    {
        $householder = $this->getOwnHouseholder();

        DB::transaction(function () use ($request, $householder) {
            $data = $request->only(['fullname', 'phone', 'email', 'family_card_number', 'notes']);

            // Preserve existing encrypted Family Card Number if left blank
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

            // Householders may not change block / unit / is_active / house_status / fee
            $householder->update($data);
        });

        return redirect()->route('household.show')
            ->with('success', 'Household information updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Residents
    // -------------------------------------------------------------------------

    public function storeResident(Request $request)
    {
        $householder = $this->getOwnHouseholder();
        $data        = $request->validate($this->residentRules());

        $data['householder_id'] = $householder->id;
        $data['is_head']        = $data['relationship'] === 'head';
        $photoPath = $this->handleResidentPhoto($request);
        if ($photoPath) $data['photo_path'] = $photoPath;
        unset($data['photo']);

        DB::transaction(function () use ($data, $householder) {
            if ($data['is_head']) {
                $householder->residents()->where('is_head', true)->update(['is_head' => false]);
            }
            Resident::create($data);
        });

        return redirect()->route('household.show')
            ->with('success', "Resident '{$data['fullname']}' added successfully.");
    }

    public function updateResident(Request $request, Resident $resident)
    {
        $householder = $this->getOwnHouseholder();
        $this->authorizeOwnResident($householder, $resident);

        $data         = $request->validate($this->residentRules());
        $becomingHead = $data['relationship'] === 'head';
        $data['is_head'] = $becomingHead;

        // Preserve existing encrypted NIK if left blank
        if (!$request->filled('nik')) {
            unset($data['nik']);
        }

        $photoPath = $this->handleResidentPhoto($request, $resident);
        if ($photoPath) $data['photo_path'] = $photoPath;
        unset($data['photo']);

        DB::transaction(function () use ($data, $householder, $resident, $becomingHead) {
            if ($becomingHead) {
                $householder->residents()
                    ->where('id', '!=', $resident->id)
                    ->where('is_head', true)
                    ->update(['is_head' => false]);
            }
            $resident->update($data);
        });

        return redirect()->route('household.show')
            ->with('success', "Resident '{$data['fullname']}' updated.");
    }

    public function destroyResident(Resident $resident)
    {
        $householder = $this->getOwnHouseholder();
        $this->authorizeOwnResident($householder, $resident);

        $name = $resident->fullname;
        if ($resident->photo_path) {
            Storage::disk('local')->delete($resident->photo_path);
        }
        $resident->delete();

        return redirect()->route('household.show')
            ->with('success', "'{$name}' has been removed from your household.");
    }

    public function setResidentHead(Resident $resident)
    {
        $householder = $this->getOwnHouseholder();
        $this->authorizeOwnResident($householder, $resident);

        DB::transaction(function () use ($householder, $resident) {
            $householder->residents()
                ->where('id', '!=', $resident->id)
                ->update(['is_head' => false]);

            $resident->update(['is_head' => true, 'relationship' => 'head']);
        });

        return redirect()->route('household.show')
            ->with('success', "{$resident->fullname} is now the Head of Family.");
    }
}
