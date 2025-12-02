<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/active-links', [App\Http\Controllers\DashboardController::class, 'activeLinks'])->name('dashboard.activeLinks');
    Route::get('dashboard/average-score-per-activity-link/{activityId}', [DashboardController::class, 'getAverageScorePerActivityLink'])->name('dashboard.averageScorePerActivityLink');
});
