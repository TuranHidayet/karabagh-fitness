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


public function freeze(Request $request, $userId, $id)
{
    try {
        $user = User::findOrFail($userId);
        $subscription = $user->subscriptions()->findOrFail($id);

        // Freeze üçün minimum müddət yoxlaması
        if ($subscription->campaign && $subscription->campaign->duration_months <= 1) {
            return CommonHelper::jsonResponse('error', 'Bu kampaniya üçün dondurma mümkün deyil', null, 403);
        }

        if ($subscription->package) {
            if (
                (isset($subscription->package->duration_months) && $subscription->package->duration_months <= 1) ||
                (isset($subscription->package->duration) && $subscription->package->duration <= 1 && empty($subscription->package->duration_days)) ||
                (isset($subscription->package->duration_days) && $subscription->package->duration_days <= 30)
            ) {
                return CommonHelper::jsonResponse('error', 'Bu paket üçün dondurma mümkün deyil', null, 403);
            }
        }

        $data = $request->validate([
            'months'     => 'required|integer|min:1',
            'start_date' => 'nullable|date'
        ]);

        $freezeStart = isset($data['start_date']) ? Carbon::parse($data['start_date']) : Carbon::now();
        $freezeEnd   = $freezeStart->copy()->addMonths($data['months']);

        $subscriptionStart = Carbon::parse($subscription->start_date);
        $subscriptionEnd   = Carbon::parse($subscription->end_date);

        if ($freezeStart->lt($subscriptionStart) || $freezeEnd->gt($subscriptionEnd)) {
            return CommonHelper::jsonResponse('error', 'Dondurma yalnız abunəlik müddəti çərçivəsində ola bilər', null, 403);
        }

        // Freeze entry yaradılır
        $freeze = UserSubscriptionFreeze::create([
            'user_id'         => $user->id,
            'subscription_id' => $subscription->id,
            'start_date'      => $freezeStart->format('Y-m-d'),
            'end_date'        => $freezeEnd->format('Y-m-d'),
            'status'          => 'inactive',
        ]);

        // Subscription-un end_date uzadılır
        $subscription->update([
            'end_date' => Carbon::parse($subscription->end_date)->addMonths($data['months'])->format('Y-m-d')
        ]);

        return CommonHelper::jsonResponse('success', 'Subscription uğurla donduruldu', $freeze, 201);

    } catch (\Exception $e) {
        \Log::error('Freeze xətası', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return CommonHelper::jsonResponse('error', 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}

public function cancelFreeze($userId, $subscriptionId)
{
    try {
        $user = User::findOrFail($userId);
        $subscription = $user->subscriptions()->findOrFail($subscriptionId);

        $freeze = UserSubscriptionFreeze::where('subscription_id', $subscription->id)
            ->where('status', 'inactive')
            ->latest('created_at')
            ->first();

        if (!$freeze) {
            return CommonHelper::jsonResponse('error', 'Dondurma tapılmadı', null, 404);
        }

        $today = Carbon::now();
        $freezeStart = Carbon::parse($freeze->start_date);
        $freezeEnd   = Carbon::parse($freeze->end_date);

        // Keçmiş günləri hesabla, amma mənfi olmayacaq
        $daysPassed = $today->greaterThan($freezeStart) ? $freezeStart->diffInDays($today) : 0;
        $totalFreezeDays = $freezeStart->diffInDays($freezeEnd);
        $remainingDays = max(0, $totalFreezeDays - $daysPassed);

        // Subscription end_date-dən qalan dondurma günlərini çıx
        if ($remainingDays > 0) {
            $subscription->update([
                'end_date' => Carbon::parse($subscription->end_date)->subDays($remainingDays)->format('Y-m-d')
            ]);
        }

        $freeze->delete(); // freeze silinir

        return CommonHelper::jsonResponse('success', 'Dondurma uğurla ləğv edildi', $subscription);

    } catch (\Exception $e) {
        \Log::error('CancelFreeze xətası', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return CommonHelper::jsonResponse('error', 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
    }
}



}
