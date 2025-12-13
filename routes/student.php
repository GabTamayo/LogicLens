<?php

use App\Enums\RoleName;
use App\Http\Controllers\Student\StudentCourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:' . RoleName::STUDENT->value])->prefix('student')->group(function () {
    Route::get('courses', [StudentCourseController::class, 'index'])->name('student.courses.index');
});
