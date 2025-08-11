<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            Log::info('Login attempt for email: ' . $request->email);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                Log::warning('User not found with email: ' . $request->email);
                return response()->json(['message' => 'İstifadəçi tapılmadı'], 401);
            }

            Log::info('User found: ' . $user->email);
            
            // Şifrə yoxlanması
            $passwordCheck = Hash::check($request->password, $user->password);
            Log::info('Password check result: ' . ($passwordCheck ? 'PASS' : 'FAIL'));
            
            if (!$passwordCheck) {
                Log::warning('Password check failed for user: ' . $user->email);
                return response()->json(['message' => 'Şifrə yanlışdır'], 401);
            }

            // Token yaratmaq
            Log::info('Creating token for user: ' . $user->email);
            $token = $user->createToken('api-token')->plainTextToken;
            Log::info('Token created successfully');

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Server xətası',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Çıxış edildi.',
        ]);
    }
}