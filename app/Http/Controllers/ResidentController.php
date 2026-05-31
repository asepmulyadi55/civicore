<?php

namespace App\Http\Controllers;

use App\Models\Householder;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResidentController extends Controller
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
            'photo'        => ['nullable', 'image', 'max:5120'],
        ];
    }

    private function handlePhoto(Request $request, Resident $resident = null): ?string
    {
        if (!$request->hasFile('photo')) return null;
        if ($resident?->photo_path) {
            Storage::disk('local')->delete($resident->photo_path);
        }
        return $request->file('photo')->store('residents', 'local');
    }

    public function store(Request $request, Householder $householder)
    {
        $data = $request->validate($this->rules());
        $data['householder_id'] = $householder->id;
        $data['is_head']        = $data['relationship'] === 'head';
        $photoPath = $this->handlePhoto($request);
        if ($photoPath) $data['photo_path'] = $photoPath;
        unset($data['photo']);

        DB::transaction(function () use ($data, $householder) {
            if ($data['is_head']) {
                $householder->residents()->where('is_head', true)->update(['is_head' => false]);
            }
            Resident::create($data);
        });

        return redirect()->route('householders.edit', $householder)
            ->with('success', "Resident '{$data['fullname']}' added successfully.");
    }

    public function update(Request $request, Householder $householder, Resident $resident)
    {
        $data = $request->validate($this->rules());
        $becomingHead    = $data['relationship'] === 'head';
        $data['is_head'] = $becomingHead;

        // Don't clobber an existing encrypted NIK if the user left the field blank.
        if (!$request->filled('nik')) {
            unset($data['nik']);
        }

        $photoPath = $this->handlePhoto($request, $resident);
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

        return redirect()->route('householders.edit', $householder)
            ->with('success', "Resident '{$data['fullname']}' updated.");
    }

    public function destroy(Householder $householder, Resident $resident)
    {
        $name = $resident->fullname;
        if ($resident->photo_path) {
            Storage::disk('local')->delete($resident->photo_path);
        }
        $resident->delete();

        return redirect()->route('householders.edit', $householder)
            ->with('success', "'{$name}' has been removed.");
    }

    /**
     * Set a specific resident as the Head of Family.
     * Clears the previous head and updates relationship to 'head'.
     */
    public function setHead(Householder $householder, Resident $resident)
    {
        DB::transaction(function () use ($householder, $resident) {
            $householder->residents()
                ->where('id', '!=', $resident->id)
                ->update(['is_head' => false]);

            $resident->update(['is_head' => true, 'relationship' => 'head']);
        });

        return redirect()->route('householders.edit', $householder)
            ->with('success', "{$resident->fullname} is now the Head of Family.");
    }
}
