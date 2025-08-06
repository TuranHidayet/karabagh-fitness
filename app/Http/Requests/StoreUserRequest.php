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
            'gender' => 'required|in:Kisi,Qadin',
            'guardian_name' => 'nullable|string',
            'guardian_birth_date' => 'nullable|date',
            'card_id' => 'required|string|unique:users,card_id',
        ];
    }
}
