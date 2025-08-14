<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PermissionController;


Route::apiResource('users', UserController::class);
Route::apiResource('roles', RoleController::class);
Route::apiResource('packages', PackageController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('campaigns', CampaignController::class);

Route::post('/admin/register', [AuthController::class, 'adminRegister']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

Route::post('/trainer/register', [AuthController::class, 'trainerRegister']);
Route::post('/trainer/login', [AuthController::class, 'trainerLogin']); 

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware(['auth:sanctum','role:admin'])->group(function() {
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
});

Route::middleware(['auth:sanctum','role:admin'])->group(function() {
    Route::get('/admins', [AuthController::class, 'getAdmins']);
});

Route::middleware(['auth:sanctum','role:admin'])->group(function() {
    Route::get('/trainers', [AuthController::class, 'getTrainers']);
});

Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole']);



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

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('campaigns', [CampaignController::class, 'store']);
    Route::put('campaigns/{campaign}', [CampaignController::class, 'update']);
    Route::delete('campaigns/{campaign}', [CampaignController::class, 'destroy']);
});



