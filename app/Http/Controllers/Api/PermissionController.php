<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;


class PermissionController extends Controller
{
    /**
     * Bütün permission-ları gətir
     */
    public function index()
    {
        return response()->json(Permission::all());
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
            'name' => $request->name,
            'guard_name' => 'sanctum', // sənin guard adın, ehtiyac varsa dəyiş
        ]);

        return response()->json([
            'message' => 'Permission əlavə edildi',
            'permission' => $permission
        ], 201);
    }

    /**
     * Mövcud permission-u yenilə
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update([
            'name' => $request->name
        ]);

        return response()->json([
            'message' => 'Permission yeniləndi',
            'permission' => $permission
        ]);
    }

    /**
     * Permission-u sil
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'message' => 'Permission silindi',
            'data' => null,
            'status' => 'success'
        ]);
    }
}
