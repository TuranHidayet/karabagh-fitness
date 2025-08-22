<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Entry;
use App\Helpers\CommonHelper;

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

        // Giriş sayı yoxlanır (yalnız "in" üçün)
        if ($request->type == 'in' && $user->remaining_entries <= 0) {
            return CommonHelper::jsonResponse('error', 'Giriş sayı bitib', null, 403);
        }

        // Entry əlavə olunur
        $entry = Entry::create([
            'user_id'     => $user->id,
            'package_id'  => $user->package_id,
            'campaign_id' => $user->campaign_id,
            'entry_type'  => $request->type ?? 'in',
        ]);

        // Qalan giriş azaldılır (yalnız "in" üçün)
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
