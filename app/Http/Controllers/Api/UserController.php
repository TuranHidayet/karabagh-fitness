<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Package;
use App\Models\Campaign;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use App\Helpers\CommonHelper;

class UserController extends Controller
{
    /**
     * Bütün istifadəçiləri göstər
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles')->get();
        return CommonHelper::jsonResponse('success', 'İstifadəçilərin siyahısı', $users);
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

        // Şəkil yükləmə
        if (!empty($data['image']) || $request->hasFile('image')) {
            $data['image'] = CommonHelper::uploadImage($data['image'] ?? $request->file('image'), 'users');
        }

        // Kampaniya və ya paket üçün tarix və giriş sayı
        $remainingEntries = 0;

        if (!empty($data['campaign_id'])) {
            $campaign = Campaign::findOrFail($data['campaign_id']);
            $data['start_date'] = $data['start_date'] ?? now()->format('Y-m-d');
            $data['end_date'] = CommonHelper::calculateEndDate($data['start_date'], $campaign);
            $data['package_id'] = null;
            $remainingEntries = $campaign->total_entries;
        } elseif (!empty($data['package_id'])) {
            $package = Package::findOrFail($data['package_id']);
            $data['start_date'] = $data['start_date'] ?? now()->format('Y-m-d');
            $data['end_date'] = CommonHelper::calculateEndDate($data['start_date'], $package);
            $remainingEntries = $package->total_entries;
        }

        $data['remaining_entries'] = $remainingEntries;

        $user = User::create($data);

        // Avtomatik subscription
        if (!empty($data['package_id']) || !empty($data['campaign_id'])) {
            $user->subscriptions()->create([
                'package_id' => $data['package_id'] ?? null,
                'campaign_id' => $data['campaign_id'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);
        }

        return CommonHelper::jsonResponse('success', 'İstifadəçi yaradıldı', $user->load('subscriptions'), 201);
    }

    /**
     * Tək istifadəçi göstər
     */
    public function show(User $user): JsonResponse
    {
        $user->load('roles', 'subscriptions');
        return CommonHelper::jsonResponse('success', 'İstifadəçi məlumatı', $user);
    }

    /**
     * İstifadəçini yenilə
     */
    /**
 * İstifadəçini yenilə
 */
public function update(UpdateUserRequest $request, User $user): JsonResponse
{
    $data = $request->validated();

    if (!empty($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    }

    // Şəkil yükləmə (köhnəni silmək də olar)
    if (!empty($data['image']) || $request->hasFile('image')) {
        $data['image'] = CommonHelper::uploadImage($data['image'] ?? $request->file('image'), 'users');
    }

    // Kart ID yoxdursa yaradılır
    if (empty($user->card_id)) {
        $data['card_id'] = Str::uuid();
    }

    // Kampaniya və ya paket varsa tarixləri və giriş sayını yenilə
    $remainingEntries = $user->remaining_entries;

    if (!empty($data['campaign_id'])) {
        $campaign = Campaign::findOrFail($data['campaign_id']);
        $data['start_date'] = $data['start_date'] ?? now()->format('Y-m-d');
        $data['end_date'] = CommonHelper::calculateEndDate($data['start_date'], $campaign);
        $data['package_id'] = null;
        $remainingEntries = $campaign->total_entries;

        // Subscription varsa yenilə, yoxsa yarat
        $subscription = $user->subscriptions()->first();
        if ($subscription) {
            $subscription->update([
                'campaign_id' => $campaign->id,
                'package_id' => null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);
        } else {
            $user->subscriptions()->create([
                'campaign_id' => $campaign->id,
                'package_id' => null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);
        }

    } elseif (!empty($data['package_id'])) {
        $package = Package::findOrFail($data['package_id']);
        $data['start_date'] = $data['start_date'] ?? now()->format('Y-m-d');
        $data['end_date'] = CommonHelper::calculateEndDate($data['start_date'], $package);
        $remainingEntries = $package->total_entries;

        $subscription = $user->subscriptions()->first();
        if ($subscription) {
            $subscription->update([
                'package_id' => $package->id,
                'campaign_id' => null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);
        } else {
            $user->subscriptions()->create([
                'package_id' => $package->id,
                'campaign_id' => null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
            ]);
        }
    }

    $data['remaining_entries'] = $remainingEntries;

    $user->update($data);

    return CommonHelper::jsonResponse('success', 'İstifadəçi yeniləndi', $user->load('subscriptions'));
}


    /**
     * İstifadəçini sil
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return CommonHelper::jsonResponse('success', 'İstifadəçi silindi');
    }
}
