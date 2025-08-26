<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Helpers\CommonHelper;

class PermissionController extends Controller
{
    /**
     * Bütün permission-ları gətir
     */
    public function index()
    {
        return CommonHelper::jsonResponse('success', 'Bütün icazələr uğurla gətirildi', Permission::all());
    }

    /**
     * Yeni permission əlavə et
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'sanctum',
        ]);

        return CommonHelper::jsonResponse('success', 'İcazə uğurla yaradıldı', $permission, 201);
    }

    /**
     * Mövcud permission-u yenilə
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        return CommonHelper::jsonResponse('success', 'İcazə uğurla yeniləndi', $permission);
    }

    /**
     * Permission-u sil
     */
    public function destroy($permissionId)
    {
        try {
            // Permission-un mövcudluğunu yoxla
            $permission = \DB::table('permissions')->where('id', $permissionId)->first();
            if (!$permission) {
                return CommonHelper::jsonResponse('error', 'İcazə tapılmadı', null, 404);
            }
            
            // Permission-un rollarını sil
            \DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            
            // Permission-un istifadəçilərini sil
            \DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            
            // Permission-u sil
            \DB::table('permissions')->where('id', $permissionId)->delete();
            
            // Cache təmizlə
            \Cache::forget('spatie.permission.cache');
            
            return CommonHelper::jsonResponse('success', 'İcazə uğurla silindi', null);
            
        } catch (\Exception $e) {
            return CommonHelper::jsonResponse('error', 'İcazə silinərkən xəta baş verdi: ' . $e->getMessage(), null, 500);
        }
    }
}
