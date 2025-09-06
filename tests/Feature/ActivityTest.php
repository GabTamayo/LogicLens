<?php

use App\Models\Activity;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('has fillable attributes', function () {
    $activity = new Activity;

    expect($activity->getFillable())->toBe(['title']);
});

it('can be created with valid attributes', function () {
    $user = User::factory()->create();

    $activity = new Activity(['title' => 'Test Activity']);
    $activity->user_id = $user->id;
    $activity->save();

    expect($activity->title)->toBe('Test Activity');
    expect($activity->user_id)->toBe($user->id);
    expect($activity->exists)->toBeTrue();
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $activity = Activity::factory()->create(['user_id' => $user->id]);

    expect($activity->user)->toBeInstanceOf(User::class);
    expect($activity->user->id)->toBe($user->id);
});

it('can be created using factory', function () {
    $activity = Activity::factory()->create();

    expect($activity)->toBeInstanceOf(Activity::class);
    expect($activity->title)->not->toBeEmpty();
    expect($activity->user_id)->toBeInt();
    expect($activity->exists)->toBeTrue();
});

test('factory creates activity with user relationship', function () {
    $user = User::factory()->create();
    $activity = Activity::factory()->create(['user_id' => $user->id]);

    expect($activity->user->id)->toBe($user->id);
    expect($user->activities)->toHaveCount(1);
    expect($user->activities->first()->id)->toBe($activity->id);
});

it('has timestamps', function () {
    $activity = Activity::factory()->create();

    expect($activity->created_at)->not->toBeNull();
    expect($activity->updated_at)->not->toBeNull();
});

it('can be mass assigned title', function () {
    $user = User::factory()->create();

    $activity = new Activity(['title' => 'Mass assigned title']);
    $activity->user_id = $user->id;
    $activity->save();

    expect($activity->title)->toBe('Mass assigned title');
    expect($activity->fresh()->title)->toBe('Mass assigned title');
});

it('cannot be mass assigned user_id', function () {
    $user = User::factory()->create();

    $activity = new Activity([
        'title' => 'Test Activity',
        'user_id' => $user->id,
    ]);

    // user_id should not be in fillable, so it should be null
    expect($activity->user_id)->toBeNull();
});

test('user can have multiple activities', function () {
    $user = User::factory()->create();

    $activity1 = Activity::factory()->create(['user_id' => $user->id]);
    $activity2 = Activity::factory()->create(['user_id' => $user->id]);

    expect($user->activities)->toHaveCount(2);
    expect($user->activities->pluck('id'))->toContain($activity1->id, $activity2->id);
});

test('activity title is required', function () {
    $user = User::factory()->create();

    expect(function () use ($user) {
        Activity::create([
            'user_id' => $user->id,
            // title is missing
        ]);
    })->toThrow(Exception::class);
});
