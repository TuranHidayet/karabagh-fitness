<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserRoleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\API\TrainerController;
use App\Http\Controllers\API\TrainerAuthController;
use App\Http\Controllers\Api\UserSubscriptionController;

// Users routes
Route::apiResource('users', UserController::class);

// Roles routes
Route::apiResource('roles', RoleController::class);
Route::post('/users/{user}/assign-role', [UserRoleController::class, 'assignRole']);

// Packages & Services routes
Route::apiResource('packages', PackageController::class);
Route::apiResource('services', ServiceController::class);

// Campaigns routes
Route::apiResource('campaigns', CampaignController::class)->only(['index', 'show']);
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('campaigns', [CampaignController::class, 'store']);
    Route::put('campaigns/{campaign}', [CampaignController::class, 'update']);
    Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy']);
});

// Admin auth routes
Route::post('/admin/register', [AuthController::class, 'adminRegister']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::middleware('auth:sanctum')->post('/admin/logout', [AuthController::class, 'logout']);

// Trainer auth routes
Route::prefix('trainer')->group(function () {
    Route::post('/login', [TrainerAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [TrainerAuthController::class, 'logout']);
});

// General auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Permission routes
Route::middleware(['auth:sanctum','role:admin'])->group(function() {
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::put('/permissions/{permission}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy']);
});

// Admin management routes
Route::middleware(['auth:sanctum','role:admin'])->group(function() {
    Route::get('/admins', [AuthController::class, 'getAdmins']);
    Route::get('/admin-panel', fn() => response()->json(['message' => 'Admin Panel']));
});

// Trainer routes
Route::middleware(['auth:sanctum','role:admin'])->group(function() {
    Route::get('/trainers', [TrainerController::class, 'index']);
    Route::get('/trainers/{id}', [TrainerController::class, 'show']);
    Route::delete('/trainers/{id}', [TrainerController::class, 'destroy']);
});
Route::middleware(['auth:sanctum', 'role:trainer'])->group(function () {
    Route::get('/trainer/profile', fn() => response()->json(['message' => 'Trainer Profili']));
});

// Payment routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{user}', [PaymentController::class, 'index']); 
});

// İstifadəçi subscription-ları
Route::prefix('users/{user}')->group(function () {
    Route::get('subscriptions', [UserSubscriptionController::class, 'index']);
    Route::post('subscriptions', [UserSubscriptionController::class, 'store']);
    Route::put('subscriptions/{id}', [UserSubscriptionController::class, 'update']);
    Route::delete('subscriptions/{id}', [UserSubscriptionController::class, 'destroy']);

    Route::post('subscriptions/{id}/renew', [UserSubscriptionController::class, 'renew']);
    Route::post('subscriptions/{id}/cancel', [UserSubscriptionController::class, 'cancel']);
    Route::post('subscriptions/{id}/freeze', [UserSubscriptionController::class, 'freeze']);
});