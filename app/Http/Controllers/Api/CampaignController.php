<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;

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
 public function store(CampaignServiceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $campaign = Campaign::create([
            'name' => $data['name'],
            'duration_months' => $data['duration_months'],
            'price' => $data['price'],
        ]);

        if (!empty($data['service_ids'])) {
            $campaign->services()->sync($data['service_ids']);
        }

        $campaign->load('services'); 

        return response()->json($campaign, 201);
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
