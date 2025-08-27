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
    $user = User::where('card_id', $cardId)->first();

    if (!$user) {
        return CommonHelper::jsonResponse('error', 'İstifadəçi tapılmadı', null, 404);
    }

    // Freeze yoxlanışı
    $today = Carbon::now();
    $activeFreeze = UserSubscriptionFreeze::where('user_id', $user->id)
        ->where('status', 'inactive')
        ->where('start_date', '<=', $today)
        ->where('end_date', '>=', $today)
        ->first();

    if ($activeFreeze) {
        return CommonHelper::jsonResponse('error', 'İstifadəçinin abonementi dondurulub', null, 403);
    }

    // Smen yoxlanışı
    $currentTime = now()->format('H:i');

    if ($user->campaign_id) {
        $campaign = Campaign::find($user->campaign_id);
        if ($campaign && $campaign->shift_start && $campaign->shift_end) {
            if ($currentTime < $campaign->shift_start || $currentTime > $campaign->shift_end) {
                return CommonHelper::jsonResponse('error', 'Bu saatlarda giriş icazəli deyil', null, 403);
            }
        }
    }

    if ($user->package_id) {
        $package = Package::find($user->package_id);
        if ($package && $package->shift_start && $package->shift_end) {
            if ($currentTime < $package->shift_start || $currentTime > $package->shift_end) {
                return CommonHelper::jsonResponse('error', 'Bu saatlarda giriş icazəli deyil', null, 403);
            }
        }
    }

    if ($request->type == 'in' && $user->remaining_entries <= 0) {
        return CommonHelper::jsonResponse('error', 'Giriş sayı bitib', null, 403);
    }

    $entry = Entry::create([
        'user_id'     => $user->id,
        'package_id'  => $user->package_id,
        'campaign_id' => $user->campaign_id,
        'entry_type'  => $request->type ?? 'in',
    ]);

    if ($request->type == 'in') {
        $user->decrement('remaining_entries');
    }

    $responseData = [
        'user_id'           => $user->id,
        'entry_id'          => $entry->id,
        'remaining_entries' => $user->remaining_entries,
        'package_id'        => $user->package_id,
        'campaign_id'       => $user->campaign_id,
        'entry_type'        => $entry->entry_type,
        'timestamp'         => $entry->created_at,
    ];

    return CommonHelper::jsonResponse(
        'success',
        $request->type == 'in' ? 'Giriş qeydə alındı' : 'Çıxış qeydə alındı',
        $responseData
    );
}
}
