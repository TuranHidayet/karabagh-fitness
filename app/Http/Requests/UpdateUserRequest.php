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
        'first_name' => 'sometimes|required|string',
        'last_name' => 'sometimes|required|string',
        'birth_date' => 'nullable|date',
        'phone' => 'nullable|string',
        'email' => 'nullable|email',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'gender' => 'sometimes|required|in:m,f',
        'guardian_name' => 'nullable|string',
        'guardian_birth_date' => 'nullable|date',
        'card_id' => 'sometimes|required|string|unique:users,card_id,' . $id,
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
