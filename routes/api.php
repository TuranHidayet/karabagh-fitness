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
use App\Http\Controllers\Api\EntryController;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
use App\Http\Controllers\LogController;


Route::middleware(['auth:sanctum','role:admin'])->group(function () {

    // Users routes
    Route::apiResource('users', UserController::class);

    // Roles routes
    Route::apiResource('roles', RoleController::class);
    Route::post('/users/{user}/assign-role', [UserRoleController::class, 'assignRole']);

    // Packages & Services routes
    Route::apiResource('packages', PackageController::class);
    Route::apiResource('services', ServiceController::class);

    // Campaigns routes
    Route::apiResource('campaigns', CampaignController::class);

    // Admin management routes
    Route::get('/admins', [AuthController::class, 'getAdmins']);
    Route::get('/admin-panel', fn() => response()->json(['message' => 'Admin Panel']));

    // Trainer routes
    Route::get('/trainers', [TrainerController::class, 'index']);
    Route::get('/trainers/{id}', [TrainerController::class, 'show']);
    Route::delete('/trainers/{id}', [TrainerController::class, 'destroy']);

    // Permission routes
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::put('/permissions/{permission}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy']);

    // Payment routes
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{user}', [PaymentController::class, 'index']); 

    // İstifadəçi subscription-ları
    Route::prefix('users/{user}')->group(function () {
        Route::get('subscriptions', [UserSubscriptionController::class, 'index']);
        Route::post('subscriptions', [UserSubscriptionController::class, 'store']);
        Route::put('subscriptions/{id}', [UserSubscriptionController::class, 'update']);
        Route::delete('subscriptions/{id}', [UserSubscriptionController::class, 'destroy']);

        Route::post('subscriptions/{id}/renew', [UserSubscriptionController::class, 'renew']);
        Route::post('subscriptions/{id}/freeze', [UserSubscriptionController::class, 'freeze']);
        Route::post('subscriptions/{id}/cancel', [UserSubscriptionController::class, 'cancelFreeze']);
    });

    // Entry routes
    Route::post('/scan-card/{cardId}', [EntryController::class, 'scanCard']);

    // Log viewer route
    Route::get('logs', [LogViewerController::class, 'index']);
    Route::get('/logs', [LogController::class, 'index']);
});

// Auth routes (admin və trainer login/logout istisna qalır)
Route::post('/admin/register', [AuthController::class, 'adminRegister']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/trainer/login', [TrainerAuthController::class, 'login']);

// Logoutlar da qorunmalıdır
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/logout', [AuthController::class, 'logout']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/trainer/logout', [TrainerAuthController::class, 'logout']);
});

// Entry routes
Route::post('/scan-card/{cardId}', [EntryController::class, 'scanCard']);



