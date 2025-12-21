<?php

use App\Http\Controllers\Api\CodeExecutionController;
use App\Http\Controllers\Teacher\SubmissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Code Execution Routes
Route::middleware(['auth', 'verified'])->prefix('api/code')->group(function () {
    Route::post('execute', [CodeExecutionController::class, 'execute'])->name('code.execute');
    Route::get('runtimes', [CodeExecutionController::class, 'runtimes'])->name('code.runtimes');
});

// Submission Routes (accessible by both teachers who created the activity and enrolled students)
Route::middleware(['auth', 'verified', 'enrolled'])->prefix('student')->group(function () {
    Route::get('submit/{token}', [SubmissionController::class, 'create'])->name('submissions.create');
    Route::post('submit/{token}', [SubmissionController::class, 'store'])->name('submissions.store');
});

require __DIR__.'/teacher.php';
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/student.php';
