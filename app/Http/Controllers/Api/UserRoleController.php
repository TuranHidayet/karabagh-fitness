<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
public function assignRole(Request $request, $userId)
    {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);
        $role = Role::findOrFail($request->role_id);

        if ($role->guard_name !== $user->guard_name) {
            return response()->json(['error' => 'Guard uyğun deyil'], 422);
        }

        $user->assignRole($role->name);

        // Cache təmizlə
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Debug üçün baxaq nə rol var
        return response()->json([
            'roles_table' => \DB::table('model_has_roles')->where('model_id', $user->id)->get(),
            'user_roles' => $user->roles
        ]);
    }
}

