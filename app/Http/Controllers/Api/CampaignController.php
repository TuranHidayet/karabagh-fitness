<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{
    /**
     * Bütün kampaniyaları göstərmək
     */
    public function index(): JsonResponse
    {
        $campaigns = Campaign::with('services')->get();

        return response()->json([
            'message' => 'Campaigns fetched successfully',
            'data' => $campaigns
        ]);
    }

    /**
     * Bir kampaniyanı göstərmək
     */
    public function show(Campaign $campaign): JsonResponse
    {
        return response()->json([
            'message' => 'Campaign fetched successfully',
            'data' => $campaign->load('services')
        ]);
    }

    /**
     * Yeni kampaniya yaratmaq
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = Campaign::create([
            'name' => $request->name,
            'duration_months' => $request->duration_months,
            'price' => $request->price,
            'total_entries' => $request->total_entries, 
        ]);

        if ($request->filled('services')) {
            $campaign->services()->attach($request->services);
        }

        return response()->json([
            'message' => 'Campaign created successfully',
            'data' => [
                'name' => $campaign->name,
                'duration_months' => $campaign->duration_months,
                'price' => $campaign->price,
                'total_entries' => $campaign->total_entries, // <- buraya əlavə et
                'services' => $campaign->services
            ]
        ], 201);
    }

    /**
     * Kampaniyanı yeniləmək
     */
    public function update(StoreCampaignRequest $request, Campaign $campaign): JsonResponse
    {
        $campaign->update([
            'name' => $request->name,
            'duration_months' => $request->duration_months,
            'price' => $request->price,
        ]);

        if ($request->filled('services')) {
            $campaign->services()->sync($request->services);
        } else {
            $campaign->services()->detach();
        }

        return response()->json([
            'message' => 'Campaign updated successfully',
            'data' => $campaign->load('services')
        ]);
    }

    /**
     * Kampaniyanı silmək
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->services()->detach();
        $campaign->delete();

        return response()->json([
            'message' => 'Campaign deleted successfully'
        ]);
    }
}
