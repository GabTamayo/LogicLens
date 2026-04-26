<?php

use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an activity link can be created for an activity', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    $activity = Activity::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->post(route('activities.links.store', $activity), [
        'name' => 'Test Token',
    ]);

    $response->assertStatus(302);

    expect(ActivityLink::where('activity_id', $activity->id)->count())->toBe(1);
    expect(ActivityLink::first()->name)->toBe('Test Token');
});
