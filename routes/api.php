<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\PackageController;


Route::apiResource('users', UserController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('packages', PackageController::class);

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum'])->post('/users/{user}/assign-role', [UserController::class, 'assignRole']);


Route::post('admin/login', [AuthController::class, 'adminLogin']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin-panel', function () {
        return response()->json(['message' => 'Admin Panel']);
    });
});


Route::middleware(['auth:sanctum', 'role:trainer'])->group(function () {
    Route::get('/trainer/profile', fn() => response()->json(['message' => 'Trainer Profili']));
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{user}', [PaymentController::class, 'index']); 
});

Route::apiResource('campaigns', CampaignController::class);

Route::get('campaigns', [CampaignController::class, 'index']);
Route::get('campaigns/{campaign}', [CampaignController::class, 'show']);

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('campaigns', [CampaignController::class, 'store']);
    Route::put('campaigns/{campaign}', [CampaignController::class, 'update']);
    Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy']);
});



