<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

    if (!empty($data['package_id'])) {
        $package = Package::find($data['package_id']);
        if ($package) {
            $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();

            // Əgər package modelində duration_days var, onu istifadə et
            if ($package->duration_days) {
                $endDate = $startDate->copy()->addDays($package->duration_days);
            } else {
                $endDate = $startDate;
            }

            $data['start_date'] = $startDate->format('Y-m-d');
            $data['end_date'] = $endDate->format('Y-m-d');
        }
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
    $data = $request->validated();

    if (!empty($data['package_id'])) {
        $package = Package::find($data['package_id']);
        if ($package) {
            $startDate = isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date']) : \Carbon\Carbon::now();

            if ($package->duration && $package->duration_type) {
                switch ($package->duration_type) {
                    case 'day':
                        $endDate = $startDate->copy()->addDays($package->duration);
                        break;
                    case 'month':
                        $endDate = $startDate->copy()->addMonths($package->duration);
                        break;
                    case 'year':
                        $endDate = $startDate->copy()->addYears($package->duration);
                        break;
                    default:
                        $endDate = $startDate;
                }
            } else {
                $endDate = $startDate;
            }

            $data['start_date'] = $startDate->format('Y-m-d');
            $data['end_date'] = $endDate->format('Y-m-d');
        }
    }

    $user->update($data);

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

    Log::info("Assigning role {$role->name} to user {$user->id}");

    $user->assignRole($role->name);

    Log::info("Role assigned");

    return response()->json(['message' => 'Rol uğurla təyin edildi.']);
}


}
