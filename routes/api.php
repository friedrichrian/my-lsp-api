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
use \App\Http\Controllers\QuestionController;

    // Hanya 10 request per menit per user/IP
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); 
    });
   

    Route::get('/jurusan', [JurusanController::class, 'index']);
    Route::get('/asesi', [UserController::class, 'show'])->middleware('auth:sanctum');
    Route::get('/asesor', [UserController::class, 'showAssesor'])->middleware('auth:sanctum');

    
    // Attachments routes
    Route::get('/form-apl01/attachment/{id}/view', [ApprovementController::class, 'viewAttachment'])
    ->name('form-apl01.attachment.view');

  // ------------ //
 // ADMIN ROUTES //
// ------------ //

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


    //Approvement routes
    Route::get('/assesment/formapl01', [ApprovementController::class, 'indexingFormApl01']);
    Route::post('/approvement/assesment/formapl01/{id}', [ApprovementController::class, 'approveFormApl01']);


    Route::post('/apl02/import', [Apl02ImportController::class, 'import']);
    Route::delete('/apl02/{id}', [Apl02ImportController::class, 'destroy']);

    Route::post('/assesment', [AssesmentController::class, 'createAssesment']);
    Route::get('/assesment', [AssesmentController::class, 'index']);
    Route::get('/assesment-asesi/byAssesment/{id}', [AssesmentAsesiController::class, 'showAssesmentAsesiByAssesment']);

    Route::post('/admin', [AdminController::class, 'store']);
    Route::get('/admin', [AdminController::class, 'index']);
    Route::put('/admin/{id}', [AdminController::class, 'update']);

    //Question routes
    Route::post('/questions/uploadWord', [QuestionController::class, 'uploadWord']);
    Route::post('/questions/answer', [QuestionController::class, 'submitAnswer']);
    Route::post('/questions', [QuestionController::class, 'createQuestion']);
    Route::put('/questions/{id}', [QuestionController::class, 'updateQuestion']);
    Route::delete('/questions/{id}', [QuestionController::class, 'deleteQuestion']);
    Route::get('/questions/skema/{skema_id}', [QuestionController::class, 'getQuestionsBySkema']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    // Routes Assesment for User / Assesi
    Route::post('/assesment/formapl01', [AssesmentController::class, 'formApl01']);
    Route::post('/assesment/formapl02', [AssesmentController::class, 'formApl02']);
    Route::post('/assesment/formak01', [AssesmentController::class, 'formAk01']);

    Route::post('/assesment/formia01', [AssesmentController::class, 'formIa01']);
    Route::post('/assesment/formak02', [AssesmentController::class, 'formAk02']);
    Route::post('/assesment/formak03', [AssesmentController::class, 'formAk03']);
    Route::post('/assesment/formak05', [AssesmentController::class, 'formAk05']);

    //Approve by Assesor
    Route::post('/approvement/assesment/formapl02/{id}', [ApprovementController::class, 'approveFormApl02ByAssesor']);

    //Approve by Assesi
    Route::get('/assesment/formak01/{id}', [AssesmentController::class, 'showAk01ByAssesi']);
    Route::get('/assesment/formia01/{id}', [AssesmentController::class, 'getIa01ByAssesi']);
    Route::get('/assesment/formak02/{id}', [AssesmentController::class, 'getAk02ByAssesi']);
    Route::get('/assesment/formak03/{id}', [AssesmentController::class, 'getAk03ByAssesi']);
    Route::get('/assesment/formak05/{id}', [AssesmentController::class, 'getAk05ByAssesi']);
    Route::get('/bukti-dokumen/view/{id}', [ApprovementController::class, 'viewAttachment'])->name('bukti-dokumen.view');

    Route::post('/user/assesment/formak01/{id}', [ApprovementController::class, 'approveFormAk01ByUser']);

    Route::get('/assesi', [AssesiController::class, 'index']);
    Route::get('/assesor', [AssesorController::class, 'index']);
    Route::get('/assesment', [AssesmentController::class, 'index']);
    Route::get('/assesment/{id}', [AssesmentController::class, 'show']);
    Route::put('/assesment/{id}', [AssesmentController::class, 'updateAssesment']);
    Route::delete('/assesment/{id}', [AssesmentController::class, 'deleteAssesment']);
    Route::get('/user/assesment-asesi/{id}', [AssesmentAsesiController::class, 'showByUser']);
    Route::post('/asesi/assesment-asesi', [AssesmentAsesiController::class, 'store']);
    Route::get('/asesi/assesment-asesi/{id}', [AssesmentAsesiController::class, 'showByAsesi']);
    Route::get('/assesor/assesment-asesi/{id}', [AssesmentAsesiController::class, 'showAssesmentAsesiByAssesment']);
    Route::get('/jurusan/{id}', [JurusanController::class, 'show']);
    //Approvement Details routes
    Route::get('/show/approvement/assesment/formapl01/{id}', [ApprovementController::class, 'showFormApl01']);
    Route::get('/user/show/approvement/assesment/formapl01/{id}', [ApprovementController::class, 'showFormApl01ByUser']);


    Route::get('/formApl01', [AssesiController::class, 'show']);
    Route::get('/apl02/{id}', [Apl02ImportController::class, 'show']);

    Route::get('/apl02/assesi/{id}', [AssesmentController::class, 'showApl02ByAssesi']);

    // Routes Assesment for Assesor
    Route::get('/schema', [Apl02ImportController::class, 'schemaIndex']);
    Route::get('/debug', [AssesmentController::class, 'debug']);

    Route::get('/status/asesi/assesment', [AssesmentController::class, 'getAssesmentAssesiStatus']);
    Route::post('/status/asesi/assesment', [AssesmentController::class, 'assesmentAssesiStatus']);

});