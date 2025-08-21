<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Package;
use Illuminate\Support\Str;
use App\Models\Campaign;
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

        $data['card_id'] = Str::uuid();

        // 📸 Şəkil yükləmə
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('users', $filename, 'public');
            $data['image'] = 'users/' . $filename;
        }

        // Kampaniya məntiqi
        if (!empty($data['campaign_id'])) {
            $campaign = Campaign::findOrFail($data['campaign_id']);

            $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
            $endDate = $startDate->copy()->addMonths($campaign->duration_months);

            $data['start_date'] = $startDate->format('Y-m-d');
            $data['end_date'] = $endDate->format('Y-m-d');

            $data['package_id'] = null;
        }
        elseif (!empty($data['package_id'])) {
            $package = Package::findOrFail($data['package_id']);
            $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();

            switch ($package->duration_type) {
                case 'day': $endDate = $startDate->copy()->addDays($package->duration); break;
                case 'month': $endDate = $startDate->copy()->addMonths($package->duration); break;
                case 'year': $endDate = $startDate->copy()->addYears($package->duration); break;
                default: $endDate = $startDate;
            }

            $data['start_date'] = $startDate->format('Y-m-d');
            $data['end_date'] = $endDate->format('Y-m-d');
        }

        $user = User::create($data);

        // Avtomatik subscription yaratmaq
        if (!empty($data['package_id']) || !empty($data['campaign_id'])) {
            $user->subscriptions()->create([
                'package_id'  => $data['package_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'start_date'  => $data['start_date'],
                'end_date'    => $data['end_date'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully with subscription.',
            'data' => $user->load('subscriptions') 
        ], 201);
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

        // Əgər password varsa, hash edirik
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        // 📸 Şəkil yükləmə (əvvəlki şəkil varsa, silmək də olar)
        if ($request->hasFile('image')) {
            // köhnə şəkil silmək (optional)
            if ($user->image && \Storage::disk('public')->exists($user->image)) {
                \Storage::disk('public')->delete($user->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('users', $filename, 'public');
            $data['image'] = 'users/' . $filename;
        }

        // Əgər package seçilibsə, tarixləri hesablamaq
        if (!empty($data['package_id'])) {
            $package = Package::find($data['package_id']);
            if ($package) {
                $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();

                if (!empty($package->duration) && !empty($package->duration_type)) {
                    switch ($package->duration_type) {
                        case 'day': $endDate = $startDate->copy()->addDays($package->duration); break;
                        case 'month': $endDate = $startDate->copy()->addMonths($package->duration); break;
                        case 'year': $endDate = $startDate->copy()->addYears($package->duration); break;
                        default: $endDate = $startDate;
                    }
                } else {
                    $endDate = $startDate;
                }

                $data['start_date'] = $startDate->format('Y-m-d');
                $data['end_date'] = $endDate->format('Y-m-d');
            }
        }

        // Əgər card_id yoxdursa, avtomatik yaradılır
        if (empty($user->card_id)) {
            $data['card_id'] = Str::uuid();
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
}
