<?php

use App\Enums\RoleName;
use App\Http\Controllers\Teacher\ActivityController;
use App\Http\Controllers\Teacher\ActivityLinkController;
use App\Http\Controllers\Teacher\CourseController;
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\DetectionController;
use App\Http\Controllers\Teacher\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:'.RoleName::TEACHER->value])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/active-links', [DashboardController::class, 'activeLinks'])->name('dashboard.activeLinks');
    Route::get('dashboard/pending-detections', [DashboardController::class, 'pendingDetections'])->name('dashboard.pendingDetections');
    Route::get('dashboard/average-score-per-activity-link/{activityId}', [DashboardController::class, 'getAverageScorePerActivityLink'])->name('dashboard.averageScorePerActivityLink');
    Route::get('dashboard/average-score', [DashboardController::class, 'getAverageScore'])->name('dashboard.averageScore');

    // Course Routes
    Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('password.confirm');
    Route::delete('/courses/{course}/students/{student}', [CourseController::class, 'removeStudent'])->name('courses.students.destroy');

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
    Route::delete('activities/{activity}/links/{link}', [ActivityLinkController::class, 'destroy'])->name('activities.links.destroy')->middleware('password.confirm');
    Route::delete('/activities/{activity}/links/{link}/deadline', [ActivityLinkController::class, 'removeDeadline'])->name('activities.links.deadline.destroy');

    // Detection routes
    Route::post('activities/{activity}/links/{link}/detect', [DetectionController::class, 'store'])->name('detections.detect');
    Route::get('detections/{detection}', [DetectionController::class, 'show'])->name('detections.show');
    Route::patch('/detections/{detection}/flag', [DetectionController::class, 'flag'])->name('detections.flag');

    // Submission Routes (teacher actions)
    Route::delete('submissions/{submission}', [SubmissionController::class, 'destroy'])->name('submissions.destroy');
});
