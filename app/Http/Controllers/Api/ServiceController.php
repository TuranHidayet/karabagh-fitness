<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use App\Helpers\CommonHelper;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return CommonHelper::jsonResponse('success', 'Xidmətlər siyahısı uğurla gətirildi', Service::all());
    }

    public function store(ServiceRequest $request): JsonResponse
    {
        $service = Service::create($request->validated());

        return CommonHelper::jsonResponse('success', 'Xidmət uğurla yaradıldı', $service, 201);
    }

    public function show(Service $service): JsonResponse
    {
        return CommonHelper::jsonResponse('success', 'Xidmət məlumatı uğurla gətirildi', $service);
    }

    public function update(ServiceRequest $request, Service $service): JsonResponse
    {
        $service->update($request->validated());

        return CommonHelper::jsonResponse('success', 'Xidmət uğurla yeniləndi', $service);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return CommonHelper::jsonResponse('success', 'Xidmət uğurla silindi', null);
    }
}
