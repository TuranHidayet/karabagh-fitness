<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function assignRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $role = Role::findOrFail($data['role_id']);

        $user->assignRole($role->name);

        return response()->json([
                'status' => 'success',
                'message' => 'Role assigned successfully.',
                'data' => [
                'user_id' => $user->id,
                'roles' => $user->roles->pluck('name') 
            ]
        ], 200);
    }


}

