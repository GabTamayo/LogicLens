<?php

use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
    Route::get('sections/create', [SectionController::class, 'create'])->name('sections.create');
    Route::post('sections', [SectionController::class, 'store'])->name('sections.store');
});
