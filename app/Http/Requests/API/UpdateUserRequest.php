<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'username'    => ['nullable', 'string', 'max:50', "unique:users,username,{$userId}"],
            'email'       => ['sometimes', 'email', 'max:255', "unique:users,email,{$userId}"],
            'password'    => ['nullable', Password::min(8)->mixedCase()->numbers()],
            'role_id'     => ['sometimes', 'uuid', 'exists:roles,id'],
            'block_id'    => ['nullable', 'uuid', 'exists:blocks,id'],
            'unit_number' => ['nullable', 'string', 'max:20'],
            'is_active'   => ['boolean'],
            'language'    => ['nullable', 'in:en,id'],
        ];
    }
}
