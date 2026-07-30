<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProjectDocumentController;
use App\Http\Controllers\Api\Exports\ProjectExportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [ProjectController::class, 'dashboard']);
        Route::get('/dashboard/chart', [ProjectController::class, 'chart']);

        Route::get('/projects/export', [ProjectExportController::class, 'excel'])->name('api.projects.export');
        Route::get('/projects/export/download', [ProjectExportController::class, 'download'])->name('api.projects.export.download');
        Route::get('/projects/{project}/export-pdf', [ProjectExportController::class, 'pdf'])->name('api.projects.export-pdf');
        Route::get('/projects/{project}/certificate', [ProjectExportController::class, 'certificate'])->name('api.projects.certificate.download');

        Route::get('/projects', [ProjectController::class, 'index']);
        Route::post('/projects', [ProjectController::class, 'store']);
        Route::get('/projects/{project}', [ProjectController::class, 'show']);
        Route::patch('/projects/{project}', [ProjectController::class, 'update']);
        Route::post('/projects/{project}/transition', [ProjectController::class, 'transition']);
        Route::get('/projects/{project}/logs', [ProjectController::class, 'logs']);
        Route::get('/projects/{project}/documents', [ProjectDocumentController::class, 'index']);
        Route::post('/projects/{project}/documents', [ProjectDocumentController::class, 'store']);
        Route::delete('/projects/{project}/documents/{document}', [ProjectDocumentController::class, 'destroy']);
    });
});
