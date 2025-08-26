<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Helpers\CommonHelper;

class RoleController extends Controller
{
    public function index()
    {
        return CommonHelper::jsonResponse('success', 'Bütün rollar uğurla gətirildi', Role::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create([
            'name'       => $request->name,
            'guard_name' => 'sanctum',
        ]);

        return CommonHelper::jsonResponse('success', 'Rol uğurla yaradıldı', $role, 201);
    }

    public function show(Role $role)
    {
        return CommonHelper::jsonResponse('success', 'Rol uğurla gətirildi', $role);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return CommonHelper::jsonResponse('success', 'Rol uğurla yeniləndi', $role);
    }

public function destroy($roleId)
{
    try {
        // Role-un mövcudluğunu yoxla
        $role = \DB::table('roles')->where('id', $roleId)->first();
        if (!$role) {
            return CommonHelper::jsonResponse('error', 'Rol tapılmadı', null, 404);
        }
        
        // Rolun icazələrini sil
        \DB::table('role_has_permissions')->where('role_id', $roleId)->delete();
        
        // Rolun istifadəçilərini sil
        \DB::table('model_has_roles')->where('role_id', $roleId)->delete();
        
        // Rolu sil
        \DB::table('roles')->where('id', $roleId)->delete();
        
        // Cache təmizlə
        \Cache::forget('spatie.permission.cache');
        
        return CommonHelper::jsonResponse('success', 'Rol uğurla silindi', null);
        
    } catch (\Exception $e) {
        return CommonHelper::jsonResponse('error', 'Rol silinərkən xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}
}
