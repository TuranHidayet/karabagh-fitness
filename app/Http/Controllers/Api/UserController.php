<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Bütün istifadəçiləri göstər
     */
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Yeni istifadəçi yarat
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = User::create($request->validated());
        return response()->json($user, 201);
    }

    /**
     * Tək istifadəçi göstər
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /**
     * İstifadəçini yenilə
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());
        return response()->json($user);
    }

    /**
     * İstifadəçini sil
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(['message' => 'İstifadəçi silindi']);
    }
}
