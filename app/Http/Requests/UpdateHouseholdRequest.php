<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHouseholdRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user   = $this->user();
        $linked = $user->resolveResident();

        // Only the linked resident can update, and only if owner-occupied
        return $linked && $linked->unit?->house_status === 'owner_occupied';
    }

    public function rules(): array
    {
        $residentId = $this->user()->resolveResident()?->id;

        return [
            'fullname'           => ['required', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:25'],
            'email'              => ['nullable', 'email', 'max:255',
                Rule::unique('residents', 'email')->ignore($residentId),
            ],
            'family_card_number' => ['nullable', 'string', 'max:20'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'photo'              => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Please enter the owner / contact name.',
        ];
    }
}
