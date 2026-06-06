<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHouseholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('householders.edit');
    }

    public function rules(): array
    {
        $householderId = $this->route('householder')->id ?? null;

        return [
            'fullname'           => ['required', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:25'],
            'email'              => ['nullable', 'email', 'max:255', Rule::unique('householders', 'email')->ignore($householderId)],
            'block_id'           => ['sometimes', 'exists:blocks,id'],
            'unit_id'            => [
                'required',
                'exists:units,id',
                Rule::unique('householders', 'unit_id')->ignore($householderId),
            ],
            'is_active'          => ['boolean'],
            'new_monthly_fee'    => ['nullable', 'numeric', 'min:0'],
            'new_fee_start'      => ['nullable', 'date_format:Y-m'],
            'family_card_number' => ['nullable', 'string', 'max:20'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'photo'              => ['nullable', 'image', 'max:5120'],
            'rent_start'         => ['nullable', 'date'],
            'rent_end'           => ['nullable', 'date', 'after_or_equal:rent_start'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Please enter the householder\'s full name.',
            'fullname.max'      => 'The name cannot exceed 100 characters.',
            'unit_id.required'  => 'Please select a unit.',
            'unit_id.exists'    => 'The selected unit does not exist.',
            'unit_id.unique'    => 'This unit is already occupied by another householder.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
