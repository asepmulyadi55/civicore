<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('blocks.edit');
    }

    public function rules(): array
    {
        $blockId = $this->route('block')?->id;

        return [
            'unit_number'  => [
                'required', 'string', 'max:30',
                Rule::unique('units')->where('block_id', $blockId),
            ],
            'house_status' => ['required', 'in:owner_occupied,vacant,rented'],
            'is_active'    => ['boolean'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_number.required' => 'Please enter a unit number.',
            'unit_number.unique'   => 'This unit number already exists in this block.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'block_id'  => $this->route('block')?->id,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
