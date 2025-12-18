<?php

use App\Enums\RoleName;
use App\Http\Controllers\Student\StudentCourseController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:'.RoleName::STUDENT->value])->prefix('student')->group(function () {
    Route::get('courses', [StudentCourseController::class, 'index'])->name('student.courses.index');
    Route::get('enroll', [StudentCourseController::class, 'enrollCourse'])->name('student.enroll');
    Route::post('enroll', [StudentCourseController::class, 'storeEnrollment'])->name('student.enroll.store');
    Route::get('courses/{course}', [StudentCourseController::class, 'show'])->name('student.courses.show');

    // Student Submission Routes
    Route::get('submit/{token}', [SubmissionController::class, 'create'])->name('submissions.create')->middleware('enrolled');
    Route::post('submit/{token}', [SubmissionController::class, 'store'])->name('submissions.store')->middleware('enrolled');
});
