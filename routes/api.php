<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectDocumentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SI Persetujuan Dokumen Kelayakan
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Dashboard (pemohon & penilai)
        Route::get('/dashboard', [ProjectController::class, 'dashboard']);

        // Projects
        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::patch('/projects/{project}', [ProjectController::class, 'update']);

        // Transisi status (submit, approve, revisi, reject)
        Route::post('/projects/{project}/transition', [ProjectController::class, 'transition']);

        // Audit log timeline per project
        Route::get('/projects/{project}/logs', [ProjectController::class, 'logs']);

        // Dokumen project
        Route::get('/projects/{project}/documents', [ProjectDocumentController::class, 'index']);
        Route::post('/projects/{project}/documents', [ProjectDocumentController::class, 'store']);
        Route::delete('/projects/{project}/documents/{document}', [ProjectDocumentController::class, 'destroy']);
    });
});
