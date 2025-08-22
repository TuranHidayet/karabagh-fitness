<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Helpers\CommonHelper;

class AuthController extends Controller
{
    /**
     * Normal istifadəçi qeydiyyatı
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $token = $user->createToken('user-token')->plainTextToken;

        return CommonHelper::jsonResponse('success', 'İstifadəçi uğurla qeydiyyatdan keçdi', [
            'user'  => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Adminləri gətir
     */
    public function getAdmins()
    {
        $admins = User::role('admin')->get();
        return CommonHelper::jsonResponse('success', 'Bütün adminlər uğurla gətirildi', $admins);
    }

    /**
     * Admin qeydiyyatı
     */
    public function adminRegister(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['card_id']  = (string) Str::uuid(); 

        $admin = User::create($validated);
        $admin->assignRole('admin');
        $token = $admin->createToken('admin-token')->plainTextToken;

        return CommonHelper::jsonResponse('success', 'Admin uğurla qeydiyyatdan keçdi', [
            'user'  => $admin,
            'token' => $token
        ], 201);
    }

    /**
     * Normal login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email və ya şifrə yanlışdır.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('user-token')->plainTextToken;

        return CommonHelper::jsonResponse('success', 'Uğurla daxil oldunuz', [
            'user'  => $user,
            'token' => $token
        ]);
    }

    /**
     * Admin login
     */
    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email və ya şifrə yanlışdır.'],
            ]);
        }

        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return CommonHelper::jsonResponse('error', 'Siz admin deyilsiniz', null, 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return CommonHelper::jsonResponse('success', 'Admin uğurla daxil oldu', [
            'user'  => $user,
            'token' => $token
        ]);
    }

    /**
     * Çıxış
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return CommonHelper::jsonResponse('success', 'Çıxış uğurla edildi', null);
    }
}
