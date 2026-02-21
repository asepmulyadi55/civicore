<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $residentId = $this->route('resident')->id ?? null;

        return [
            'fullname' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:25'],
            'block_id' => ['required', 'exists:blocks,id'],
            'unit_number' => [
                'required',
                'string',
                'max:20',
                // Unit must be unique per block, ignoring this resident
                Rule::unique('residents')->where('block_id', $this->block_id)->ignore($residentId),
            ],
            'is_active' => ['boolean'],
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
