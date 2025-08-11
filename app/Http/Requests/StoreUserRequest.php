<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'gender' => 'required|in:m,f',
            'guardian_name' => 'nullable|string',
            'guardian_birth_date' => 'nullable|date',
            'card_id' => 'required|string|unique:users,card_id',
            'password' => 'nullable|string|min:6',
            'package_id' => 'nullable|exists:packages,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'promo_code' => 'nullable|string|max:50',
        ];
    }
}
