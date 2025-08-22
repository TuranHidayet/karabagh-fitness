<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Entry;

class EntryController extends Controller
{
    public function scanCard(Request $request, $cardId)
    {
        $user = User::where('card_id', $cardId)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tapilmadi',
                'data' => null
            ], 404);
        }

        // Giriş sayı yoxla (yalnız in üçün)
if ($request->type == 'in' && $user->remaining_entries <= 0) {
    return response()->json([
        'status' => 'error',
        'message' => 'Giriş sayı bitib',
        'data' => null
    ], 403);
}

// Entry əlavə et
$entry = Entry::create([
    'user_id' => $user->id,
    'package_id' => $user->package_id,
    'campaign_id' => $user->campaign_id,
    'entry_type' => $request->type ?? 'in',
]);

// Total entries-ni azald (yalnız in üçün)
$remaining = null;
if ($request->type == 'in') {
    $user->decrement('remaining_entries'); // burada user-in öz qalan girişini azaldırıq
    $remaining = $user->remaining_entries;
}

        return response()->json([
            'status' => 'success',
            'message' => $request->type == 'in' ? 'Giriş qeydə alındı' : 'Çıxış qeydə alındı',
            'data' => [
                'user_id' => $user->id,
                'entry_id' => $entry->id,
                'remaining_entries' => $user->remaining_entries,
                'package_id' => $user->package_id,
                'campaign_id' => $user->campaign_id,
                'entry_type' => $entry->entry_type,
                'timestamp' => $entry->created_at
            ]
        ], 200);
    }

}
