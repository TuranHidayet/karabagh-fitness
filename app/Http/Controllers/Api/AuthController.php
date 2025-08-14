<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\TrainerRegisterRequest;

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

        return response()->json([
            'message' => 'İstifadəçi uğurla yaradıldı',
            'user'    => $user,
            'token'   => $token
        ], 201);
    }

    public function getAdmins()
    {
        // "admin" roluna sahib bütün user-ləri gətir
        $admins = User::role('admin')->get();

        return response()->json($admins);
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

        // Şifrəni hash edirik
        $validated['password'] = bcrypt($validated['password']);

        $validated['card_id'] = (string) Str::uuid(); 

        // Yeni user yaradılır
        $admin = User::create($validated);

        // Admin rolunu təyin edirik
        $admin->assignRole('admin');

        // Token yaradırıq
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Admin uğurla yaradıldı',
            'user'    => $admin,
            'token'   => $token
        ], 201);
    }

    public function trainerRegister(TrainerRegisterRequest $request)
    {
        $data = $request->validated();

        // Şifrəni hash edirik
        $data['password'] = bcrypt($data['password']);
        $data['card_id'] = (string) Str::uuid(); 

        // Yeni user yaradılır
        $trainer = User::create($data);

        // Trainer rolunu təyin edirik
        $trainer->assignRole('trainer');

        // Token yaradırıq
        $token = $trainer->createToken('trainer-token')->plainTextToken;

        return response()->json([
            'message' => 'Trainer uğurla yaradıldı',
            'user'    => $trainer,
            'token'   => $token
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
                'email' => ['Email və ya şifrə səhvdir.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
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
                'email' => ['Email və ya şifrə səhvdir.'],
            ]);
        }

        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json(['message' => 'Siz admin deyilsiniz.'], 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token
        ]);
    }

    public function trainerLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message'=>'Email və ya şifrə səhvdir'], 401);
        }

        $user = Auth::user();

        if (!$user->hasRole('trainer')) {
            return response()->json(['message'=>'Siz trainer deyilsiniz'], 403);
        }

        $token = $user->createToken('trainer-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token
        ]);
    }

    public function getTrainers()
    {
        // "trainer" roluna sahib bütün user-ləri gətir
        $trainers = User::role('trainer')->get();

        return response()->json($trainers);
    }

    /**
     * Çıxış
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Çıxış edildi.']);
    }
}
