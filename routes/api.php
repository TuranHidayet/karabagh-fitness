<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\PaymentController;


Route::apiResource('users', UserController::class);
Route::apiResource('roles', RoleController::class);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware(['auth:sanctum'])->post('/users/{user}/assign-role', [UserController::class, 'assignRole']);



Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin-panel', fn() => response()->json(['message' => 'Admin Panel']));
});

Route::middleware(['auth:sanctum', 'role:trainer'])->group(function () {
    Route::get('/trainer/profile', fn() => response()->json(['message' => 'Trainer Profili']));
});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{user}', [PaymentController::class, 'index']); 
});




