<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHouseholdRequest;
use App\Models\Block;
use App\Models\FamilyMember;
use App\Models\Resident;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve the authenticated user's linked resident, or abort 403.
     */
    private function getOwnResident(): Resident
    {
        $resident = auth()->user()->resident;

        if (!$resident) {
            abort(403, 'No household record is linked to your account. Please contact your administrator.');
        }

        return $resident;
    }

    /**
     * Ensure the given FamilyMember belongs to the linked resident, or abort 403.
     */
    private function authorizeOwnMember(Resident $resident, FamilyMember $familyMember): void
    {
        if ($familyMember->resident_id !== $resident->id) {
            abort(403, 'This family member does not belong to your household.');
        }
    }

    /**
     * Shared validation rules for family members.
     */
    private function memberRules(): array
    {
        return [
            'fullname'     => ['required', 'string', 'max:100'],
            'relationship' => ['required', 'in:head,spouse,child,parent,tenant,other'],
            'nik'          => ['nullable', 'string', 'max:20'],
            'birth_date'   => ['nullable', 'date', 'before_or_equal:today'],
            'gender'       => ['nullable', 'in:male,female'],
            'education'    => ['nullable', 'in:none,elementary,junior_high,senior_high,associate,bachelor,master,doctorate,other'],
            'occupation'   => ['nullable', 'string', 'max:100'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Household info
    // ─────────────────────────────────────────────────────────────────────────

    public function show()
    {
        $user     = auth()->user();
        $resident = $this->getOwnResident();

        $resident->load([
            'block',
            'familyMembers',
            'feeHistories' => fn($q) => $q->orderByDesc('effective_from'),
        ]);

        $canManageInfo        = $resident->house_status === 'owner_occupied';
        $canManageFamilyMembers = true;

        $blocks             = Block::active()->orderBy('name')->get();
        $currency           = Setting::get('currency_symbol', 'Rp');
        $updateRoute        = route('household.update');
        $familyMembersBase  = url('/household/family-members');
        $backRoute          = route('overview');
        $showRevealButtons  = false;   // residents never see full sensitive data
        $isOwnHousehold     = true;

        return view('residents.edit', compact(
            'resident', 'blocks', 'currency',
            'canManageInfo', 'canManageFamilyMembers',
            'updateRoute', 'familyMembersBase',
            'backRoute', 'showRevealButtons', 'isOwnHousehold'
        ));
    }

    public function update(UpdateHouseholdRequest $request)
    {
        $resident = $this->getOwnResident();

        DB::transaction(function () use ($request, $resident) {
            $data = $request->only(['fullname', 'phone', 'email', 'family_card_number', 'notes']);

            // Preserve existing encrypted Family Card Number if left blank
            if (!$request->filled('family_card_number')) {
                unset($data['family_card_number']);
            }

            // Residents may not change block / unit / is_active / house_status / fee
            $resident->update($data);
        });

        return redirect()->route('household.show')
            ->with('success', 'Household information updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Family Members
    // ─────────────────────────────────────────────────────────────────────────

    public function storeFamilyMember(Request $request)
    {
        $resident = $this->getOwnResident();
        $data     = $request->validate($this->memberRules());

        $data['resident_id'] = $resident->id;
        $data['is_head']     = $data['relationship'] === 'head';

        DB::transaction(function () use ($data, $resident) {
            if ($data['is_head']) {
                $resident->familyMembers()->where('is_head', true)->update(['is_head' => false]);
            }
            FamilyMember::create($data);
        });

        return redirect()->route('household.show')
            ->with('success', "Family member '{$data['fullname']}' added successfully.");
    }

    public function updateFamilyMember(Request $request, FamilyMember $familyMember)
    {
        $resident = $this->getOwnResident();
        $this->authorizeOwnMember($resident, $familyMember);

        $data         = $request->validate($this->memberRules());
        $becomingHead = $data['relationship'] === 'head';
        $data['is_head'] = $becomingHead;

        // Preserve existing encrypted NIK if left blank
        if (!$request->filled('nik')) {
            unset($data['nik']);
        }

        DB::transaction(function () use ($data, $resident, $familyMember, $becomingHead) {
            if ($becomingHead) {
                $resident->familyMembers()
                    ->where('id', '!=', $familyMember->id)
                    ->where('is_head', true)
                    ->update(['is_head' => false]);
            }
            $familyMember->update($data);
        });

        return redirect()->route('household.show')
            ->with('success', "Family member '{$data['fullname']}' updated.");
    }

    public function destroyFamilyMember(FamilyMember $familyMember)
    {
        $resident = $this->getOwnResident();
        $this->authorizeOwnMember($resident, $familyMember);

        $name = $familyMember->fullname;
        $familyMember->delete();

        return redirect()->route('household.show')
            ->with('success', "'{$name}' has been removed from your household.");
    }

    public function setFamilyMemberHead(FamilyMember $familyMember)
    {
        $resident = $this->getOwnResident();
        $this->authorizeOwnMember($resident, $familyMember);

        DB::transaction(function () use ($resident, $familyMember) {
            $resident->familyMembers()
                ->where('id', '!=', $familyMember->id)
                ->update(['is_head' => false]);

            $familyMember->update(['is_head' => true, 'relationship' => 'head']);
        });

        return redirect()->route('household.show')
            ->with('success', "{$familyMember->fullname} is now the Head of Family.");
    }
}
