<?php

use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('teacher can delete a submission', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $activity = Activity::factory()->create(['user_id' => $teacher->id]);
    $activityLink = ActivityLink::factory()->create(['activity_id' => $activity->id]);
    $submission = Submission::factory()->create(['activity_link_id' => $activityLink->id]);

    $this->actingAs($teacher);

    expect(Submission::count())->toBe(1);

    $response = $this->delete(route('submissions.destroy', $submission));

    $response->assertStatus(302);
    expect(Submission::count())->toBe(0);
});

test('student cannot delete a submission', function () {
    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $activity = Activity::factory()->create(['user_id' => $teacher->id]);
    $activityLink = ActivityLink::factory()->create(['activity_id' => $activity->id]);
    $submission = Submission::factory()->create(['activity_link_id' => $activityLink->id]);

    $this->actingAs($student);

    expect(Submission::count())->toBe(1);

    $response = $this->delete(route('submissions.destroy', $submission));

    $response->assertForbidden();
    expect(Submission::count())->toBe(1);
});

test('guest cannot delete a submission', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $activity = Activity::factory()->create(['user_id' => $teacher->id]);
    $activityLink = ActivityLink::factory()->create(['activity_id' => $activity->id]);
    $submission = Submission::factory()->create(['activity_link_id' => $activityLink->id]);

    expect(Submission::count())->toBe(1);

    $response = $this->delete(route('submissions.destroy', $submission));

    $response->assertStatus(302);
    expect(Submission::count())->toBe(1);
});
