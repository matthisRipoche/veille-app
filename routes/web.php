<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ReportController::class, 'home'])->name('home');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/{date}', [ReportController::class, 'show'])->name('reports.show');
