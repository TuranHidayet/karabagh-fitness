<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Package;
use App\Models\UserSubscriptionFreeze;
use App\Models\Campaign;
use Carbon\Carbon;
use App\Helpers\CommonHelper;

class UserSubscriptionController extends Controller
{
    public function index(User $user)
    {
        $subscriptions = $user->subscriptions()->latest()->get();

        return CommonHelper::jsonResponse('success', 'User subscription-ları uğurla gətirildi', $subscriptions);
    }

    public function store(Request $request, User $user)
    {
        $data = $request->validate([
            'package_id'  => 'nullable|exists:packages,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'start_date'  => 'nullable|date',
        ]);

        $startDate = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();

        if (!empty($data['campaign_id'])) {
            $campaign = Campaign::findOrFail($data['campaign_id']);
            $endDate = $startDate->copy()->addMonths($campaign->duration_months);
        } elseif (!empty($data['package_id'])) {
            $package = Package::findOrFail($data['package_id']);
            switch ($package->duration_type) {
                case 'day':   $endDate = $startDate->copy()->addDays($package->duration); break;
                case 'month': $endDate = $startDate->copy()->addMonths($package->duration); break;
                case 'year':  $endDate = $startDate->copy()->addYears($package->duration); break;
                default:      $endDate = $startDate;
            }
        } else {
            return CommonHelper::jsonResponse('error', 'Package və ya campaign tələb olunur', null, 422);
        }

        $subscription = $user->subscriptions()->create([
            'package_id'  => $data['package_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'start_date'  => $startDate->format('Y-m-d'),
            'end_date'    => $endDate->format('Y-m-d'),
        ]);

        $user->update([
            'start_date'  => $startDate->format('Y-m-d'),
            'end_date'    => $endDate->format('Y-m-d'),
            'package_id'  => $data['package_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
        ]);

        return CommonHelper::jsonResponse('success', 'Subscription uğurla yaradıldı', $subscription, 201);
    }

    // public function update(Request $request, User $user, $id)
    // {
    //     $subscription = $user->subscriptions()->findOrFail($id);

    //     $data = $request->validate([
    //         'start_date' => 'nullable|date',
    //         'end_date'   => 'nullable|date',
            
    //     ]);

    //     $subscription->update($data);

    //     return CommonHelper::jsonResponse('success', 'Subscription uğurla yeniləndi', $subscription);
    // }

    public function destroy(User $user, $id)
    {
        $subscription = $user->subscriptions()->findOrFail($id);
        $subscription->delete();

        return CommonHelper::jsonResponse('success', 'Subscription uğurla silindi', null);
    }

  public function renew(Request $request, User $user, $id)
{
    $subscription = $user->subscriptions()->findOrFail($id);

    $data = $request->validate([
        'package_id'  => 'nullable|exists:packages,id',
        'campaign_id' => 'nullable|exists:campaigns,id',
        'start_date'  => 'nullable|date',
    ]);

    // Başlanğıc tarix
    $startDate = isset($data['start_date'])
        ? Carbon::parse($data['start_date'])
        : Carbon::parse($subscription->end_date)->addDay();

    // Giriş sayını və bitiş tarixini təyin edirik
    $remainingEntries = 0;

    if (!empty($data['campaign_id'])) {
        $campaign = Campaign::findOrFail($data['campaign_id']);
        $endDate = $startDate->copy()->addMonths($campaign->duration_months);
        $remainingEntries = $campaign->total_entries;
    } elseif (!empty($data['package_id'])) {
        $package = Package::findOrFail($data['package_id']);
        switch ($package->duration_type) {
            case 'day':   $endDate = $startDate->copy()->addDays($package->duration); break;
            case 'month': $endDate = $startDate->copy()->addMonths($package->duration); break;
            case 'year':  $endDate = $startDate->copy()->addYears($package->duration); break;
            default:      $endDate = $startDate;
        }
        $remainingEntries = $package->total_entries ?? 0;
    } else {
        return CommonHelper::jsonResponse('error', 'Yeni package və ya campaign tələb olunur', null, 422);
    }

    // Yeni subscription yaradılır (tarixi saxlayırıq)
    $newSubscription = $user->subscriptions()->create([
        'package_id'  => $data['package_id'] ?? null,
        'campaign_id' => $data['campaign_id'] ?? null,
        'start_date'  => $startDate->format('Y-m-d'),
        'end_date'    => $endDate->format('Y-m-d'),
    ]);

    // User məlumatlarını update edirik, remaining_entries də yenilənir
    $user->update([
        'package_id'        => $data['package_id'] ?? null,
        'campaign_id'       => $data['campaign_id'] ?? null,
        'start_date'        => $startDate->format('Y-m-d'),
        'end_date'          => $endDate->format('Y-m-d'),
        'remaining_entries' => $remainingEntries,
    ]);

    return CommonHelper::jsonResponse('success', 'Subscription uğurla yeniləndi', $newSubscription, 201);
}


    public function cancel(User $user, $id)
    {
        $subscription = $user->subscriptions()->findOrFail($id);
        $subscription->update([
            'end_date' => Carbon::now()->format('Y-m-d')
        ]);

        return CommonHelper::jsonResponse('success', 'Subscription uğurla ləğv edildi', $subscription);
    }

public function freeze(Request $request, User $user, $id)
{
    try {
        // Debug: User və subscription-u yoxlayaq
        \Log::info('Freeze başladı', ['user_id' => $user->id, 'subscription_id' => $id]);
        
        $subscription = $user->subscriptions()->findOrFail($id);
        \Log::info('Subscription tapıldı', ['subscription' => $subscription->toArray()]);

        $data = $request->validate([
            'months'     => 'required|integer|min:1',
            'start_date' => 'nullable|date'
        ]);
        \Log::info('Validation keçdi', ['data' => $data]);

        $freezeStart = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
        $freezeEnd   = $freezeStart->copy()->addMonths($data['months']);
        
        \Log::info('Tarixlər hazırlandı', [
            'freeze_start' => $freezeStart->format('Y-m-d'),
            'freeze_end' => $freezeEnd->format('Y-m-d')
        ]);

        // Birbaşa create etməyə çalışaq
        $freeze = UserSubscriptionFreeze::create([
            'user_id'        => $user->id,
            'subscription_id' => $subscription->id,
            'start_date'     => $freezeStart->format('Y-m-d'),
            'end_date'       => $freezeEnd->format('Y-m-d'),
            'status'         => 'inactive',
        ]);
        
        \Log::info('Freeze yaradıldı', ['freeze' => $freeze->toArray()]);

        $subscription->update([
            'end_date' => Carbon::parse($subscription->end_date)->addMonths($data['months'])->format('Y-m-d')
        ]);
        
        \Log::info('Subscription yeniləndi');

        return CommonHelper::jsonResponse('success', 'Subscription uğurla donduruldu', $freeze, 201);
        
    } catch (\Exception $e) {
        \Log::error('Freeze xətası', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        return CommonHelper::jsonResponse('error', 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}
}
