<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssesiController;
use App\Http\Controllers\AssesorController;
use App\Http\Controllers\AssesmentController;
use App\Http\Controllers\ApprovementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Apl02ImportController;

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
    Route::get('/jurusan', [AuthController::class, 'jurusanIndex']);

    Route::get('/user', [UserController::class, 'show'])->middleware('auth:sanctum');
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    //Assesi routes
    Route::post('/assesi', [AssesiController::class, 'store']);
    Route::put('/assesi/{id}', [AssesiController::class, 'update']);
    Route::delete('/assesi/{id}', [AssesiController::class, 'destroy']);

    //Assesor routes
    Route::post('/assesor', [AssesorController::class, 'store']);
    Route::get('/assesor/{id}', [AssesorController::class, 'show']);
    Route::put('/assesor/{id}', [AssesorController::class, 'update']);
    Route::delete('/assesor/{id}', [AssesorController::class, 'destroy']);

    //Approvement Details routes
    Route::get('/approvement/assesment/formapl01/{id}', [ApprovementController::class, 'showFormApl01']);

    //Approvement routes
    Route::post('/approvement/assesment/formapl01/{id}', [ApprovementController::class, 'approveFormApl01']);

    // Attachments routes
    Route::get('/form-apl01/attachment/{id}/view', [ApprovementController::class, 'viewAttachment'])
    ->name('form-apl01.attachment.view');

    Route::post('/apl02/import', [Apl02ImportController::class, 'import']);
    Route::get('/apl02/{id}', [Apl02ImportController::class, 'show']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Routes Assesment for User / Assesi
    Route::post('/assesment/formapl01', [AssesmentController::class, 'formApl01']);
    Route::post('/assesment/formapl02', [AssesmentController::class, 'formApl02']);
    Route::get('/assesi', [AssesiController::class, 'index']);
    Route::get('/assesor', [AssesorController::class, 'index']);

    // Routes Assesment for Assesor
    Route::get('/schema', [Apl02ImportController::class, 'schemaIndex'])->middleware('approve');
    Route::get('/debug', [AssesmentController::class, 'debug']);

    // Routes FR.IA.01 - Ceklis Observasi Aktivitas
    Route::prefix('fr-ia01')->group(function () {
        Route::post('/sessions', [App\Http\Controllers\FrIa01Controller::class, 'store']);
        Route::get('/sessions/{id}', [App\Http\Controllers\FrIa01Controller::class, 'show']);
        Route::put('/sessions/{id}/result', [App\Http\Controllers\FrIa01Controller::class, 'updateAssessmentResult']);
        Route::delete('/sessions/{id}', [App\Http\Controllers\FrIa01Controller::class, 'destroy']);
        
        Route::put('/kuks/{id}', [App\Http\Controllers\FrIa01Controller::class, 'updateKuk']);
        Route::put('/groups/{id}/feedback', [App\Http\Controllers\FrIa01Controller::class, 'updateGroupFeedback']);
    });
});
