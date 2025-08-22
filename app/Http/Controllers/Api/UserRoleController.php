<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Helpers\CommonHelper;

class UserRoleController extends Controller
{
    public function assignRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $role = Role::findOrFail($data['role_id']);
        $user->assignRole($role->name);

        return CommonHelper::jsonResponse('success', 'Role uğurla təyin edildi', [
            'user_id' => $user->id,
            'roles'   => $user->roles->pluck('name')
        ]);
    }
}
