<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Helpers\CommonHelper;

class TrainerAuthController extends Controller
{
    // Trainer login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $trainer = User::role('trainer')->where('email', $request->email)->first();

        if (!$trainer || !Hash::check($request->password, $trainer->password)) {
            return CommonHelper::jsonResponse('error', 'Email və ya şifrə səhvdir', null, 401);
        }

        // Token yarat
        $token = $trainer->createToken('trainer-token')->plainTextToken;

        return CommonHelper::jsonResponse('success', 'Trainer uğurla daxil oldu', [
            'trainer' => $trainer,
            'token'   => $token
        ]);
    }

    // Logout (token silmək)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return CommonHelper::jsonResponse('success', 'Trainer uğurla çıxış etdi', null);
    }
}
