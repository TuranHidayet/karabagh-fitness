<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Models\Campaign;

class CampaignController extends Controller
{
    /**
     * Bütün kampaniyaları göstər
     */
    public function index()
    {
        return response()->json(Campaign::all());
    }

    /**
     * Yeni kampaniya əlavə et
     */
    public function store(StoreCampaignRequest $request)
    {
        $campaign = Campaign::create($request->validated());

        return response()->json([
            'message' => 'Kampaniya uğurla yaradıldı',
            'data' => $campaign
        ], 201);
    }

    /**
     * Tək kampaniyanı göstər
     */
    public function show(Campaign $campaign)
    {
        return response()->json($campaign);
    }

    /**
     * Kampaniyanı yenilə
     */
    public function update(StoreCampaignRequest $request, Campaign $campaign)
    {
        $campaign->update($request->validated());

        return response()->json([
            'message' => 'Kampaniya uğurla yeniləndi',
            'data' => $campaign
        ]);
    }

    /**
     * Kampaniyanı sil
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return response()->json([
            'message' => 'Kampaniya uğurla silindi'
        ]);
    }
}
