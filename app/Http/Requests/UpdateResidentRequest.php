<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('residents.edit');
    }

    public function rules(): array
    {
        $residentId = $this->route('resident')->id ?? null;

        return [
            'fullname'           => ['required', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:25'],
            'email'              => ['nullable', 'email', 'max:255', Rule::unique('residents', 'email')->ignore($residentId)],
            'block_id'           => ['sometimes', 'exists:blocks,id'],
            'unit_id'            => [
                'required',
                'exists:units,id',
                Rule::unique('residents', 'unit_id')->ignore($residentId),
            ],
            'is_active'          => ['boolean'],
            'new_monthly_fee'    => ['nullable', 'numeric', 'min:0'],
            'new_fee_start'      => ['nullable', 'date_format:Y-m'],
            'family_card_number' => ['nullable', 'string', 'max:20'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'photo'              => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Please enter the resident\'s full name.',
            'fullname.max'      => 'The name cannot exceed 100 characters.',
            'unit_id.required'  => 'Please select a unit.',
            'unit_id.exists'    => 'The selected unit does not exist.',
            'unit_id.unique'    => 'This unit is already occupied by another resident.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
