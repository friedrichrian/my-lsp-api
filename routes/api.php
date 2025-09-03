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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AssesmentAsesiController;
use App\Http\Controllers\JurusanController;

Route::get('/user', function (Request $request) {
    return auth()->user();
})->middleware('auth:sanctum');

    // Hanya 10 request per menit per user/IP
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); 
    });
   

    Route::get('/jurusan', [JurusanController::class, 'index']);
    Route::get('/user', [UserController::class, 'show'])->middleware('auth:sanctum');

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

    //Jurusan routes
    Route::post('/jurusan', [JurusanController::class, 'store']);
    Route::put('/jurusan/{id}', [JurusanController::class, 'update']);
    Route::delete('/jurusan/{id}', [JurusanController::class, 'destroy']);

    //Approvement Details routes
    Route::get('/approvement/assesment/formapl01/{id}', [ApprovementController::class, 'showFormApl01']);

    //Approvement routes
    Route::get('/assesment/formapl01', [ApprovementController::class, 'indexingFormApl01']);
    Route::post('/approvement/assesment/formapl01/{id}', [ApprovementController::class, 'approveFormApl01']);

    // Attachments routes
    Route::get('/form-apl01/attachment/{id}/view', [ApprovementController::class, 'viewAttachment'])
    ->name('form-apl01.attachment.view');

    Route::post('/apl02/import', [Apl02ImportController::class, 'import']);
    Route::delete('/apl02/{id}', [Apl02ImportController::class, 'destroy']);

    Route::post('/assesment', [AssesmentController::class, 'createAssesment']);
    Route::get('/assesment', [AssesmentController::class, 'index']);

    Route::post('/admin', [AdminController::class, 'store']);
    Route::get('/admin', [AdminController::class, 'index']);
    Route::put('/admin/{id}', [AdminController::class, 'update']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Routes Assesment for User / Assesi
    Route::post('/assesment/formapl01', [AssesmentController::class, 'formApl01']);
    Route::post('/assesment/formapl02', [AssesmentController::class, 'formApl02']);
    Route::post('/assesment/formak01', [AssesmentController::class, 'formAk01']);
    Route::post('/assesment/formia01', [AssesmentController::class, 'formIa01']);

    //Approve by user
    Route::post('/user/assesment/formak01/{id}', [ApprovementController::class, 'approveFormAk01ByUser']);

    Route::get('/assesi', [AssesiController::class, 'index']);
    Route::get('/assesor', [AssesorController::class, 'index']);
    Route::get('/assesment', [AssesmentController::class, 'index']);
    Route::get('/assesment/{id}', [AssesmentController::class, 'show']);
    Route::put('/assesment/{id}', [AssesmentController::class, 'updateAssesment']);
    Route::delete('/assesment/{id}', [AssesmentController::class, 'deleteAssesment']);
    Route::post('/assesment/asesi', [AssesmentAsesiController::class, 'store']);
    Route::get('/assesment/asesi/{id}', [AssesmentAsesiController::class, 'showByAsesi']);
    Route::get('/jurusan/{id}', [JurusanController::class, 'show']);

    Route::get('/formApl01', [AssesiController::class, 'show']);
    Route::get('/apl02/{id}', [Apl02ImportController::class, 'show']);

    // Routes Assesment for Assesor
    Route::get('/schema', [Apl02ImportController::class, 'schemaIndex'])->middleware('approve');
    Route::get('/debug', [AssesmentController::class, 'debug']);

    Route::get('/status/asesi/assesment', [AssesmentController::class, 'getAssesmentAssesiStatus']);
    Route::post('/status/asesi/assesment', [AssesmentController::class, 'assesmentAssesiStatus']);

});
