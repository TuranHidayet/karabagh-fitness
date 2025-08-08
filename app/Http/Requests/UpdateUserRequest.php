<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('user'); // 'user' = route param adı

        return [
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'gender' => 'sometimes|in:Kisi,Qadin',
            'guardian_name' => 'nullable|string',
            'guardian_birth_date' => 'nullable|date',
            'card_id' => 'sometimes|string|unique:users,card_id,' . $id,
            'password' => 'nullable|string|min:6'
        ];
    }
}
