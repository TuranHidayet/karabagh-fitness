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
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    /**
     * Yeni istifadəçi yarat
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
         $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user = User::create($data);

        return response()->json($user, 201);
    }

    /**
     * Tək istifadəçi göstər
     */
    public function show(User $user): JsonResponse
    {
        $user->load('roles');
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

    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::find($request->role_id);

        $user->roles()->syncWithoutDetaching([$role->id]);

        return response()->json(['message' => 'Rol uğurla təyin edildi.']);
    }


}
