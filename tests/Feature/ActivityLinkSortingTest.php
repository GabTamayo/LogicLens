<?php

use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Detection;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('submissions can be sorted by newest first', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create(['user_id' => $user->id]);
    $link = ActivityLink::factory()->create(['activity_id' => $activity->id]);

    $oldSubmission = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Alice',
        'created_at' => now()->subDays(2),
    ]);

    $newSubmission = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Bob',
        'created_at' => now(),
    ]);

    $response = $this->get(route('activities.links.show', [
        'activity' => $activity->id,
        'link' => $link->id,
        'sort' => 'newest',
    ]));

    $response->assertSuccessful();

    $submissions = $response->viewData('page')['props']['submissions']['data'] ?? null;
    expect($submissions)->not->toBeNull();
    expect($submissions[0]['id'])->toBe($newSubmission->id);
    expect($submissions[1]['id'])->toBe($oldSubmission->id);
});

test('submissions can be sorted by oldest first', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create(['user_id' => $user->id]);
    $link = ActivityLink::factory()->create(['activity_id' => $activity->id]);

    $oldSubmission = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Alice',
        'created_at' => now()->subDays(2),
    ]);

    $newSubmission = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Bob',
        'created_at' => now(),
    ]);

    $response = $this->get(route('activities.links.show', [
        'activity' => $activity->id,
        'link' => $link->id,
        'sort' => 'oldest',
    ]));

    $response->assertSuccessful();

    $submissions = $response->viewData('page')['props']['submissions']['data'] ?? null;
    expect($submissions)->not->toBeNull();
    expect($submissions[0]['id'])->toBe($oldSubmission->id);
    expect($submissions[1]['id'])->toBe($newSubmission->id);
});

test('submissions can be sorted by name A to Z', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create(['user_id' => $user->id]);
    $link = ActivityLink::factory()->create(['activity_id' => $activity->id]);

    $charlie = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Charlie',
    ]);

    $alice = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Alice',
    ]);

    $bob = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Bob',
    ]);

    $response = $this->get(route('activities.links.show', [
        'activity' => $activity->id,
        'link' => $link->id,
        'sort' => 'name_asc',
    ]));

    $response->assertSuccessful();

    $submissions = $response->viewData('page')['props']['submissions']['data'] ?? null;
    expect($submissions)->not->toBeNull();
    expect($submissions[0]['id'])->toBe($alice->id);
    expect($submissions[1]['id'])->toBe($bob->id);
    expect($submissions[2]['id'])->toBe($charlie->id);
});

test('submissions can be sorted by name Z to A', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create(['user_id' => $user->id]);
    $link = ActivityLink::factory()->create(['activity_id' => $activity->id]);

    $charlie = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Charlie',
    ]);

    $alice = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Alice',
    ]);

    $bob = Submission::factory()->create([
        'activity_link_id' => $link->id,
        'student_name' => 'Bob',
    ]);

    $response = $this->get(route('activities.links.show', [
        'activity' => $activity->id,
        'link' => $link->id,
        'sort' => 'name_desc',
    ]));

    $response->assertSuccessful();

    $submissions = $response->viewData('page')['props']['submissions']['data'] ?? null;
    expect($submissions)->not->toBeNull();
    expect($submissions[0]['id'])->toBe($charlie->id);
    expect($submissions[1]['id'])->toBe($bob->id);
    expect($submissions[2]['id'])->toBe($alice->id);
});

test('detections can be sorted by similarity score high to low', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create(['user_id' => $user->id]);
    $link = ActivityLink::factory()->create(['activity_id' => $activity->id]);

    $sub1 = Submission::factory()->create(['activity_link_id' => $link->id]);
    $sub2 = Submission::factory()->create(['activity_link_id' => $link->id]);
    $sub3 = Submission::factory()->create(['activity_link_id' => $link->id]);

    $lowScore = Detection::factory()->create([
        'activity_link_id' => $link->id,
        'submission_a_id' => $sub1->id,
        'submission_b_id' => $sub2->id,
        'avg_score' => 0.3,
        'flagged' => false,
    ]);

    $highScore = Detection::factory()->create([
        'activity_link_id' => $link->id,
        'submission_a_id' => $sub2->id,
        'submission_b_id' => $sub3->id,
        'avg_score' => 0.9,
        'flagged' => false,
    ]);

    $response = $this->get(route('activities.links.show', [
        'activity' => $activity->id,
        'link' => $link->id,
        'tab' => 'detection',
        'sort' => 'score_desc',
    ]));

    $response->assertSuccessful();

    $detections = $response->viewData('page')['props']['detections']['data'] ?? null;
    expect($detections)->not->toBeNull();
    expect($detections[0]['id'])->toBe($highScore->id);
    expect($detections[1]['id'])->toBe($lowScore->id);
});

test('detections can be sorted by similarity score low to high', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create(['user_id' => $user->id]);
    $link = ActivityLink::factory()->create(['activity_id' => $activity->id]);

    $sub1 = Submission::factory()->create(['activity_link_id' => $link->id]);
    $sub2 = Submission::factory()->create(['activity_link_id' => $link->id]);
    $sub3 = Submission::factory()->create(['activity_link_id' => $link->id]);

    $lowScore = Detection::factory()->create([
        'activity_link_id' => $link->id,
        'submission_a_id' => $sub1->id,
        'submission_b_id' => $sub2->id,
        'avg_score' => 0.3,
        'flagged' => false,
    ]);

    $highScore = Detection::factory()->create([
        'activity_link_id' => $link->id,
        'submission_a_id' => $sub2->id,
        'submission_b_id' => $sub3->id,
        'avg_score' => 0.9,
        'flagged' => false,
    ]);

    $response = $this->get(route('activities.links.show', [
        'activity' => $activity->id,
        'link' => $link->id,
        'tab' => 'detection',
        'sort' => 'score_asc',
    ]));

    $response->assertSuccessful();

    $detections = $response->viewData('page')['props']['detections']['data'] ?? null;
    expect($detections)->not->toBeNull();
    expect($detections[0]['id'])->toBe($lowScore->id);
    expect($detections[1]['id'])->toBe($highScore->id);
});
