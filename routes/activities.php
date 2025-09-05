<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('activities', [ActivityController::class, 'index'])
    ->middleware('auth')
    ->name('activities.index');

?>
