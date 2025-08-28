<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Entry;
use App\Models\Campaign;
use Carbon\Carbon;
use App\Models\Package;
use App\Helpers\CommonHelper;
use App\Models\UserSubscriptionFreeze;

class EntryController extends Controller
{
    /**
     * Kart oxutma ilə giriş/çıxış
     */
    public function scanCard(Request $request, $cardId)
    {
        try {
            $user = User::where('card_id', $cardId)->first();

            if (!$user) {
                return CommonHelper::jsonResponse('error', 'İstifadəçi tapılmadı', null, 404);
            }

            $subscription = $user->subscriptions()->latest()->first();

            if (!$subscription) {
                return CommonHelper::jsonResponse('error', 'Aktiv abunəlik yoxdur', null, 404);
            }

            $today = Carbon::today();
            $start = Carbon::parse($subscription->start_date);
            $end   = Carbon::parse($subscription->end_date);

            // Freeze yoxlaması
            $activeFreeze = UserSubscriptionFreeze::where('subscription_id', $subscription->id)
                ->where('status', 'inactive')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->first();

            if ($activeFreeze) {
                return CommonHelper::jsonResponse('error', 'Abunəlik dondurulub', null, 403);
            }

            // Start / End date yoxlaması
            if ($today->lt($start)) { // today < start_date
                return CommonHelper::jsonResponse('error', 'Hələ abunəlik aktiv deyil', null, 403);
            }

            if ($today->gt($end)) { // today > end_date
                return CommonHelper::jsonResponse('error', 'Abunəlik bitib', null, 403);
            }

            // Shift yoxlaması
            $currentTime = now()->format('H:i');
            if ($subscription->campaign_id) {
                $campaign = Campaign::find($subscription->campaign_id);
                if ($campaign && $campaign->shift_start && $campaign->shift_end) {
                    if ($currentTime < $campaign->shift_start || $currentTime > $campaign->shift_end) {
                        return CommonHelper::jsonResponse('error', 'Bu saatlarda giriş icazəli deyil', null, 403);
                    }
                }
            }
            if ($subscription->package_id) {
                $package = Package::find($subscription->package_id);
                if ($package && $package->shift_start && $package->shift_end) {
                    if ($currentTime < $package->shift_start || $currentTime > $package->shift_end) {
                        return CommonHelper::jsonResponse('error', 'Bu saatlarda giriş icazəli deyil', null, 403);
                    }
                }
            }

            // Remaining entries yoxlaması
            if ($request->type == 'in' && $user->remaining_entries <= 0) {
                return CommonHelper::jsonResponse('error', 'Giriş sayı bitib', null, 403);
            }

            // Entry yaradılır
            $entry = Entry::create([
                'user_id'     => $user->id,
                'package_id'  => $subscription->package_id,
                'campaign_id' => $subscription->campaign_id,
                'entry_type'  => $request->type ?? 'in',
            ]);

            if ($request->type == 'in') {
                $user->decrement('remaining_entries');
            }

            $responseData = [
                'user_id'           => $user->id,
                'entry_id'          => $entry->id,
                'remaining_entries' => $user->remaining_entries,
                'package_id'        => $subscription->package_id,
                'campaign_id'       => $subscription->campaign_id,
                'entry_type'        => $entry->entry_type,
                'timestamp'         => $entry->created_at,
            ];

            return CommonHelper::jsonResponse(
                'success',
                $request->type == 'in' ? 'Giriş qeydə alındı' : 'Çıxış qeydə alındı',
                $responseData
            );

        } catch (\Exception $e) {
            \Log::error('ScanCard xətası', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return CommonHelper::jsonResponse('error', 'Xəta baş verdi: ' . $e->getMessage(), null, 500);
        }
    }
}
