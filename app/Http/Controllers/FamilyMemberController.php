<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilyMemberController extends Controller
{
    private function rules(): array
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

    public function store(Request $request, Resident $resident)
    {
        $data = $request->validate($this->rules());
        $data['resident_id'] = $resident->id;
        $data['is_head']     = $data['relationship'] === 'head';

        DB::transaction(function () use ($data, $resident) {
            if ($data['is_head']) {
                $resident->familyMembers()->where('is_head', true)->update(['is_head' => false]);
            }
            FamilyMember::create($data);
        });

        return redirect()->route('residents.edit', $resident)
            ->with('success', "Family member '{$data['fullname']}' added successfully.");
    }

    public function update(Request $request, Resident $resident, FamilyMember $familyMember)
    {
        $data = $request->validate($this->rules());
        $becomingHead    = $data['relationship'] === 'head';
        $data['is_head'] = $becomingHead;

        // Don't clobber an existing encrypted NIK if the user left the field blank.
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

        return redirect()->route('residents.edit', $resident)
            ->with('success', "Family member '{$data['fullname']}' updated.");
    }

    public function destroy(Resident $resident, FamilyMember $familyMember)
    {
        $name = $familyMember->fullname;
        $familyMember->delete();

        return redirect()->route('residents.edit', $resident)
            ->with('success', "'{$name}' has been removed.");
    }

    /**
     * Set a specific family member as the Head of Family.
     * Clears the previous head and updates relationship to 'head'.
     */
    public function setHead(Resident $resident, FamilyMember $familyMember)
    {
        DB::transaction(function () use ($resident, $familyMember) {
            $resident->familyMembers()
                ->where('id', '!=', $familyMember->id)
                ->update(['is_head' => false]);

            $familyMember->update(['is_head' => true, 'relationship' => 'head']);
        });

        return redirect()->route('residents.edit', $resident)
            ->with('success', "{$familyMember->fullname} is now the Head of Family.");
    }
}
