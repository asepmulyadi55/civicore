<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // resident can upload their own proof
    }

    public function rules(): array
    {
        return [
            'resident_id'       => ['required', 'uuid', 'exists:residents,id'],
            'payment_month'     => ['required', 'date_format:Y-m'],
            'amount'            => ['required', 'numeric', 'min:0'],
            'payment_method_id' => ['required', 'uuid', 'exists:payment_methods,id'],
            'proof'             => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
            'notes'             => ['nullable', 'string', 'max:500'],
        ];
    }
}
