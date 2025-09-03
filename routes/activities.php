<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/activities', function () {
    return Inertia::render('Activities');
})->middleware('auth')->name('activities');

?>
