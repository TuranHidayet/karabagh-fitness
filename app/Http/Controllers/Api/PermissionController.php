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
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return CommonHelper::jsonResponse('success', 'İcazə uğurla silindi', null);
    }
}
