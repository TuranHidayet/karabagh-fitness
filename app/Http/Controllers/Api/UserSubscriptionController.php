<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Package;
use App\Models\Campaign;
use Carbon\Carbon;

class UserSubscriptionController extends Controller
{
    /**
     * İstifadəçinin bütün subscription-larını göstər
     */
    public function index(User $user)
    {
        $subscriptions = $user->subscriptions()->latest()->get();

        return response()->json([
            'status' => 'success',
            'message' => 'User subscriptions fetched successfully.',
            'data' => $subscriptions
        ], 200);
    }

    /**
     * Yeni subscription yarat
     */
    public function store(Request $request, User $user)
    {
        $data = $request->validate([
            'package_id' => 'nullable|exists:packages,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'start_date' => 'nullable|date',
        ]);

        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();

        if (!empty($data['campaign_id'])) {
            $campaign = Campaign::findOrFail($data['campaign_id']);
            $endDate = $startDate->copy()->addMonths($campaign->duration_months);
        } elseif (!empty($data['package_id'])) {
            $package = Package::findOrFail($data['package_id']);
            switch ($package->duration_type) {
                case 'day': $endDate = $startDate->copy()->addDays($package->duration); break;
                case 'month': $endDate = $startDate->copy()->addMonths($package->duration); break;
                case 'year': $endDate = $startDate->copy()->addYears($package->duration); break;
                default: $endDate = $startDate;
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Package or campaign required.',
                'data' => null
            ], 422);
        }

        // Yeni subscription yazırıq
        $subscription = $user->subscriptions()->create([
            'package_id' => $data['package_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
        ]);

        // User-in aktiv vaxtını da yeniləyirik
        $user->update([
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
            'package_id' => $data['package_id'] ?? null,
            'campaign_id'=> $data['campaign_id'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription created successfully.',
            'data' => $subscription
        ], 201);
    }

    /**
     * Mövcud subscription-u yenilə
     */
    public function update(Request $request, User $user, $id)
    {
        $subscription = $user->subscriptions()->findOrFail($id);

        $data = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $subscription->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription updated successfully.',
            'data' => $subscription
        ], 200);
    }

    /**
     * Subscription-u sil
     */
    public function destroy(User $user, $id)
    {
        $subscription = $user->subscriptions()->findOrFail($id);
        $subscription->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription deleted successfully.',
            'data' => null
        ], 200);
    }

    /**
     * Subscription-u uzat (renew)
     */
    public function renew(Request $request, User $user, $id)
{
    $subscription = $user->subscriptions()->findOrFail($id);

    $data = $request->validate([
        'package_id'  => 'nullable|exists:packages,id',
        'campaign_id' => 'nullable|exists:campaigns,id',
        'start_date'  => 'nullable|date',
    ]);

    // Yeni start date default olaraq artıq bitmiş tarixdən başlayır
    $startDate = isset($data['start_date']) 
        ? Carbon::parse($data['start_date']) 
        : Carbon::parse($subscription->end_date)->addDay();

    // Yeni end date-i hesabla
    if (!empty($data['campaign_id'])) {
        $campaign = Campaign::findOrFail($data['campaign_id']);
        $endDate = $startDate->copy()->addMonths($campaign->duration_months);
    } elseif (!empty($data['package_id'])) {
        $package = Package::findOrFail($data['package_id']);
        switch ($package->duration_type) {
            case 'day': $endDate = $startDate->copy()->addDays($package->duration); break;
            case 'month': $endDate = $startDate->copy()->addMonths($package->duration); break;
            case 'year': $endDate = $startDate->copy()->addYears($package->duration); break;
            default: $endDate = $startDate;
        }
    } else {
        return response()->json([
            'status' => 'error',
            'message' => 'Please provide a new package_id or campaign_id to renew.',
            'data' => null
        ], 422);
    }

    // Yeni subscription yazırıq
    $newSubscription = $user->subscriptions()->create([
        'package_id'  => $data['package_id'] ?? null,
        'campaign_id' => $data['campaign_id'] ?? null,
        'start_date'  => $startDate->format('Y-m-d'),
        'end_date'    => $endDate->format('Y-m-d'),
    ]);

    // User-in əsas subscription məlumatını da yeniləyirik
    $user->update([
        'package_id'  => $data['package_id'] ?? null,
        'campaign_id' => $data['campaign_id'] ?? null,
        'start_date'  => $startDate->format('Y-m-d'),
        'end_date'    => $endDate->format('Y-m-d'),
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Subscription renewed with new package/campaign successfully.',
        'data' => $newSubscription
    ], 201);
}


    /**
     * Subscription-u ləğv et (cancel)
     */
    public function cancel(User $user, $id)
    {
        $subscription = $user->subscriptions()->findOrFail($id);

        $subscription->update([
            'end_date' => Carbon::now()->format('Y-m-d')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription cancelled successfully.',
            'data' => $subscription
        ], 200);
    }

    /**
 * Subscription-u dondur (freeze)
 */
    public function freeze(Request $request, User $user, $id)
    {
        $subscription = $user->subscriptions()->findOrFail($id);

        $data = $request->validate([
            'months' => 'required|integer|min:1', 
            'start_date' => 'nullable|date'
        ]);

        $freezeStart = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
        $freezeEnd = $freezeStart->copy()->addMonths($data['months']);

        // Freeze qeydiyyatı yaradılır
        $freeze = $subscription->freezes()->create([
            'user_id' => $user->id,
            'start_date' => $freezeStart->format('Y-m-d'),
            'end_date' => $freezeEnd->format('Y-m-d'),
            'status' => 'inactive',
        ]);

        // Subscription-un end_date-ni freeze müddəti qədər uzadırıq
        $subscription->update([
            'end_date' => Carbon::parse($subscription->end_date)->addMonths($data['months'])->format('Y-m-d')
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription frozen successfully.',
            'data' => $freeze
        ], 201);
    }

}
