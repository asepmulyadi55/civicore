<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('residents.edit');
    }

    public function rules(): array
    {
        return [
            'fullname'           => ['sometimes', 'string', 'max:255'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'email'              => ['nullable', 'email', 'max:255'],
            'block_id'           => ['sometimes', 'uuid', 'exists:blocks,id'],
            'unit_id'            => ['nullable', 'uuid', 'exists:units,id'],
            'is_active'          => ['boolean'],
            'family_card_number' => ['nullable', 'string', 'max:30'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'photo'              => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ];
    }
}
