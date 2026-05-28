<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'username'    => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', Password::min(8)->mixedCase()->numbers()],
            'role_id'     => ['required', 'uuid', 'exists:roles,id'],
            'block_id'    => ['nullable', 'uuid', 'exists:blocks,id'],
            'unit_number' => ['nullable', 'string', 'max:20'],
            'is_active'   => ['boolean'],
            'language'    => ['nullable', 'in:en,id'],
        ];
    }
}
