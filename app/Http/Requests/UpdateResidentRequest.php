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
            'block_id'           => ['required', 'exists:blocks,id'],
            'unit_number'        => [
                'required',
                'string',
                'max:20',
                Rule::unique('residents')->where('block_id', $this->block_id)->ignore($residentId),
            ],
            'is_active'          => ['boolean'],
            'new_monthly_fee'    => ['nullable', 'numeric', 'min:0'],
            'new_fee_start'      => ['nullable', 'date_format:Y-m'],
            'family_card_number' => ['nullable', 'string', 'max:20'],
            'house_status'       => ['required', 'in:owner_occupied,vacant,rented'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Please enter the resident\'s full name.',
            'fullname.max' => 'The name cannot exceed 100 characters.',
            'block_id.required' => 'Please select a block for this resident.',
            'block_id.exists' => 'The selected block does not exist.',
            'unit_number.required' => 'Please enter a unit number (e.g. A-101).',
            'unit_number.max' => 'The unit number cannot exceed 20 characters.',
            'unit_number.unique' => 'This unit number is already taken in the selected block.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
