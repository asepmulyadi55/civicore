<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('residents.create');
    }

    public function rules(): array
    {
        return [
            'fullname' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255', 'unique:residents,email'],
            'block_id' => ['required', 'exists:blocks,id'],
            'unit_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('residents')->where('block_id', $this->block_id),
            ],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'fee_start' => ['required', 'date_format:Y-m'],
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
            'monthly_fee.required' => 'Please enter the monthly fee amount.',
            'monthly_fee.numeric' => 'The monthly fee must be a valid number.',
            'monthly_fee.min' => 'The monthly fee cannot be negative.',
            'fee_start.required' => 'Please select the month this fee takes effect.',
            'fee_start.date_format' => 'Please enter a valid month (e.g. 2024-01).',
        ];
    }
}
