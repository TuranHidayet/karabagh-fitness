<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Auth yoxlanışı varsa true yoxsa false qoy
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'payment_type' => 'required|in:cash,card',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'promo_code' => 'nullable|string',
        ];
    }
}
