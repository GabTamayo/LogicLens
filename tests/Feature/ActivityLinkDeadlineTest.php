<?php

use App\Models\ActivityLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(RefreshDatabase::class);

it('closes expired activity links when the command is run', function () {
    // Create 2 links: one expired, one future
    $expiredLink = ActivityLink::factory()->expired()->create(['is_open' => true]);
    $futureLink = ActivityLink::factory()->withExpiration(5)->create(['is_open' => true]);

    // Run the Artisan command
    Artisan::call('activity-links:close-expired');

    // Reload from database
    $expiredLink->refresh();
    $futureLink->refresh();

    // Expired link should be closed
    expect($expiredLink->is_open)->toBeFalse();

    // Future link should remain open
    expect($futureLink->is_open)->toBeTrue();
});
