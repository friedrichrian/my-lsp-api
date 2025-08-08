<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssesiController;
use App\Http\Controllers\AssesorController;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['throttle:10,1'])->group(function () {
    // Hanya 10 request per menit per user/IP
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); 
    });

    Route::get('/user', [UserController::class, 'show'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    //Assesi routes
    Route::post('/assesi', [AssesiController::class, 'store']);
    Route::get('/assesi', [AssesiController::class, 'index']);
    Route::put('/assesi/{id}', [AssesiController::class, 'update']);
    Route::delete('/assesi/{id}', [AssesiController::class, 'destroy']);

    //Assesor routes
    Route::post('/assesor', [AssesorController::class, 'store']);
    Route::get('/assesor', [AssesorController::class, 'index']);
    Route::get('/assesor/{id}', [AssesorController::class, 'show']);
    Route::put('/assesor/{id}', [AssesorController::class, 'update']);
    Route::delete('/assesor/{id}', [AssesorController::class, 'destroy']);
});