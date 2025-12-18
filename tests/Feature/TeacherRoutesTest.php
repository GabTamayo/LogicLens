<?php

use Illuminate\Support\Facades\Route;

it('registers teacher routes and points to teacher controllers', function () {
    $route = Route::getRoutes()->getByName('activities.index');
    expect($route)->not->toBeNull();
    expect($route->uri)->toBe('activities');
    expect(str_contains($route->action['controller'], 'Teacher\\ActivityController'))->toBeTrue();

    $route = Route::getRoutes()->getByName('dashboard');
    expect($route)->not->toBeNull();
    expect($route->uri)->toBe('dashboard');
    expect(str_contains($route->action['controller'], 'Teacher\\DashboardController'))->toBeTrue();
});
