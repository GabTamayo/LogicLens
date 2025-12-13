<?php

use App\Enums\RoleName;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:' . RoleName::TEACHER->value])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/active-links', [App\Http\Controllers\DashboardController::class, 'activeLinks'])->name('dashboard.activeLinks');
    Route::get('dashboard/pending-detections', [App\Http\Controllers\DashboardController::class, 'pendingDetections'])->name('dashboard.pendingDetections');
    Route::get('dashboard/average-score-per-activity-link/{activityId}', [DashboardController::class, 'getAverageScorePerActivityLink'])->name('dashboard.averageScorePerActivityLink');
    Route::get('dashboard/average-score', [DashboardController::class, 'getAverageScore'])->name('dashboard.averageScore');
});
