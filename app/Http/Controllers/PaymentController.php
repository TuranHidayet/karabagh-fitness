<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\User;

class PaymentController extends Controller
{
    public function store(PaymentRequest $request)
    {
        $data = $request->validated();

        $payment = Payment::create($data);

        $user = User::find($data['user_id']);
        $user->package_id = $data['package_id'];
        $user->save();

        return response()->json([
            'message' => 'Ödəniş uğurla əlavə edildi',
            'payment' => $payment,
        ], 201);
    }

    public function index(User $user)
    {
        $payments = $user->payments()->with('package')->get();

        return response()->json($payments);
    }
}
