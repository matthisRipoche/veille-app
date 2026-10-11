<?php

use App\Http\Controllers\Api\ReportApiController;
use Illuminate\Support\Facades\Route;

// API de dépôt : POST /api/reports (en-tête Authorization: Bearer <API_TOKEN>)
Route::post('/reports', [ReportApiController::class, 'store'])
    ->middleware('veille.token')
    ->name('api.reports.store');
