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
            'birth_date' => 'required|date',
            'phone' => 'required|string',
            'email' => 'required|email',
            'image' => 'nullable|string',
            'gender' => 'required|in:m,f',
            'guardian_name' => 'nullable|string',
            'guardian_birth_date' => 'nullable|date',
            'card_id' => 'nullable|string|unique:users,card_id',
            'password' => 'nullable|string|min:6',
            'package_id' => 'nullable|exists:packages,id|required_without:campaign_id',
            'campaign_id' => 'nullable|exists:campaigns,id|required_without:package_id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'promo_code' => 'nullable|string|max:50',
            'payment_method' => 'nullable|in:card,cash',
        ];
    }
}
