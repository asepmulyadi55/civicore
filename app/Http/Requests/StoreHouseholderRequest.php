<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHouseholderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('householders.create');
    }

    public function rules(): array
    {
        return [
            'fullname'           => ['required', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:25'],
            'email'              => ['nullable', 'email', 'max:255', 'unique:householders,email'],
            'block_id'           => ['required', 'exists:blocks,id'],
            'unit_id'            => [
                'required',
                'exists:units,id',
                'unique:householders,unit_id',
            ],
            'monthly_fee'        => ['required', 'numeric', 'min:0'],
            'fee_start'          => ['required', 'date_format:Y-m'],
            'family_card_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required'  => 'Please enter the householder\'s full name.',
            'fullname.max'       => 'The name cannot exceed 100 characters.',
            'block_id.required'  => 'Please select a block for this householder.',
            'block_id.exists'    => 'The selected block does not exist.',
            'unit_id.required'   => 'Please select a unit.',
            'unit_id.exists'     => 'The selected unit does not exist.',
            'unit_id.unique'     => 'This unit is already occupied by another householder.',
            'monthly_fee.required' => 'Please enter the monthly fee amount.',
            'monthly_fee.numeric'  => 'The monthly fee must be a valid number.',
            'monthly_fee.min'      => 'The monthly fee cannot be negative.',
            'fee_start.required'   => 'Please select the month this fee takes effect.',
            'fee_start.date_format' => 'Please enter a valid month (e.g. 2024-01).',
        ];
    }
}
