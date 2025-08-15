<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Xidmətlər siyahısı',
            'data' => Service::all()
        ], 200);
    }

    public function store(ServiceRequest $request): JsonResponse
    {
        $service = Service::create($request->validated());

        return response()->json([
            'message' => 'Xidmət yaradıldı',
            'data' => $service
        ], 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json([
            'message' => 'Xidmət məlumatı',
            'data' => $service
        ], 200);
    }

    public function update(ServiceRequest $request, Service $service): JsonResponse
    {
        $service->update($request->validated());

        return response()->json([
            'message' => 'Xidmət yeniləndi',
            'data' => $service
        ], 200);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json([
            'message' => 'Xidmət silindi'
        ], 200);
    }
}
