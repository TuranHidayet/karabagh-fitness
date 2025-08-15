<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TrainerAuthController extends Controller
{
    // Trainer login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $trainer = User::role('trainer')->where('email', $request->email)->first();

        if (!$trainer || !Hash::check($request->password, $trainer->password)) {
            return response()->json(['message' => 'Email və ya şifrə səhvdir'], 401);
        }

        // Token yarat
        $token = $trainer->createToken('trainer-token')->plainTextToken;

        return response()->json([
            'trainer' => $trainer,
            'token' => $token
        ], 200);
    }

    // Logout (token silmək)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout edildi'], 200);
    }
}

