<?php

use App\Enums\RoleName;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ActivityLinkController;
use App\Http\Controllers\DetectionController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:' . RoleName::TEACHER->value])->group(function () {
    // Activity Routes
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::get('activities/{activity}', [ActivityController::class, 'show'])->name('activities.show');
    Route::patch('activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy')->middleware('password.confirm');

    // Activity Link Routes
    Route::post('activities/{activity}/links', [ActivityLinkController::class, 'store'])->name('activities.links.store');
    Route::patch('activities/{activity}/links/{link}', [ActivityLinkController::class, 'update'])->name('activities.links.update');
    Route::get('activities/{activity}/links/{link}', [ActivityLinkController::class, 'show'])->name('activities.links.show');
    Route::delete('activities/{activity}/links/{link}', [ActivityLinkController::class, 'destroy'])->name('submissions.destroy')->middleware('password.confirm');
    Route::delete('/activities/{activity}/links/{link}/deadline', [ActivityLinkController::class, 'removeDeadline'])->name('activities.links.deadline.destroy');

    // Detection routes
    Route::post('activities/{activity}/links/{link}/detect', [DetectionController::class, 'store'])->name('detections.detect');
    Route::get('detections/{detection}', [DetectionController::class, 'show'])->name('detections.show');
    Route::patch('/detections/{detection}/flag', [DetectionController::class, 'flag'])->name('detections.flag');
});

// Student Submission Routes
Route::get('submit{token}', [SubmissionController::class, 'create'])->name('submissions.create');
Route::post('submit/{token}', [SubmissionController::class, 'store'])->name('submissions.store');
