<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use App\Helpers\CommonHelper;

class CampaignController extends Controller
{
    /**
     * Bütün kampaniyaları göstərmək
     */
    public function index(): JsonResponse
    {
        $campaigns = Campaign::with('services')->get();
        return CommonHelper::jsonResponse('success', 'Bütün kampaniyalar uğurla gətirildi', $campaigns);
    }

    /**
     * Bir kampaniyanı göstərmək
     */
    public function show(Campaign $campaign): JsonResponse
    {
        return CommonHelper::jsonResponse('success', 'Kampaniya uğurla gətirildi', $campaign->load('services'));
    }

    /**
     * Yeni kampaniya yaratmaq
     */
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = Campaign::create([
            'name'            => $request->name,
            'duration_months' => $request->duration_months,
            'price'           => $request->price,
            'total_entries'   => $request->total_entries, 
            'shift_start'     => $request->shift_start,  
            'shift_end'       => $request->shift_end,
        ]);

        if ($request->filled('services')) {
            $campaign->services()->attach($request->services);
        }

        return CommonHelper::jsonResponse(
            'success',
            'Kampaniya uğurla yaradıldı',
            $campaign->load('services'),
            201
        );
    }

    /**
     * Kampaniyanı yeniləmək
     */
    public function update(StoreCampaignRequest $request, Campaign $campaign): JsonResponse
    {
        $campaign->update([
            'name'            => $request->name,
            'duration_months' => $request->duration_months,
            'price'           => $request->price,
            'shift_start'     => $request->shift_start,  
            'shift_end'       => $request->shift_end,
        ]);

        if ($request->filled('services')) {
            $campaign->services()->sync($request->services);
        } else {
            $campaign->services()->detach();
        }

        return CommonHelper::jsonResponse('success', 'Kampaniya uğurla yeniləndi', $campaign->load('services'));
    }

    /**
     * Kampaniyanı silmək
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        $campaign->services()->detach();
        $campaign->delete();

        return CommonHelper::jsonResponse('success', 'Kampaniya uğurla silindi', null);
    }
}
