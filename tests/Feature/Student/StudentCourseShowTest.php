<?php

use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => RoleName::TEACHER->value]);
    Role::firstOrCreate(['name' => RoleName::STUDENT->value]);
});

it('shows only open activities in activities tab', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $activity = Activity::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $openLink = ActivityLink::factory()->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => true,
    ]);

    $closedLink = ActivityLink::factory()->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => false,
    ]);

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=activities")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('activities.data', 1)
            ->where('activities.data.0.id', $openLink->id)
            ->where('activities.data.0.is_open', true)
        );
});

it('shows closed activities in completed tab', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $activity = Activity::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $closedLink = ActivityLink::factory()->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => false,
    ]);

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=completed")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('completedActivities.data', 1)
            ->where('completedActivities.data.0.id', $closedLink->id)
            ->where('completedActivities.data.0.is_open', false)
        );
});

it('shows submitted activities in completed tab', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $activity = Activity::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $activityLink = ActivityLink::factory()->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => true,
    ]);

    Submission::factory()->create([
        'user_id' => $student->id,
        'activity_link_id' => $activityLink->id,
    ]);

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=completed")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('completedActivities.data', 1)
            ->where('completedActivities.data.0.id', $activityLink->id)
            ->where('completedActivities.data.0.is_open', true)
        );
});

it('shows both closed and submitted activities in completed tab', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $activity = Activity::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $closedLink = ActivityLink::factory()->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => false,
    ]);

    $submittedLink = ActivityLink::factory()->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => true,
    ]);

    Submission::factory()->create([
        'user_id' => $student->id,
        'activity_link_id' => $submittedLink->id,
    ]);

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=completed")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('completedActivities.data', 2)
        );
});

it('excludes closed activities from activities tab', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $activity = Activity::factory()->create([
        'user_id' => $teacher->id,
    ]);

    ActivityLink::factory()->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => false,
    ]);

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=activities")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('activities.data', 0)
        );
});

it('supports infinite scroll pagination for activities', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $activity = Activity::factory()->create([
        'user_id' => $teacher->id,
    ]);

    ActivityLink::factory(10)->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => true,
    ]);

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=activities")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('activities.data', 5)
            ->has('activities.links')
            ->has('activities.next_page_url')
            ->where('activities.current_page', 1)
            ->where('activities.per_page', 5)
        );

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=activities&page=2")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('activities.data', 5)
            ->where('activities.current_page', 2)
        );
});

it('supports infinite scroll pagination for completed activities', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
    ]);

    $course->students()->attach($student->id, ['enrolled_at' => now()]);

    $activity = Activity::factory()->create([
        'user_id' => $teacher->id,
    ]);

    ActivityLink::factory(10)->create([
        'activity_id' => $activity->id,
        'course_id' => $course->id,
        'is_open' => false,
    ]);

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=completed")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('completedActivities.data', 5)
            ->has('completedActivities.links')
            ->has('completedActivities.next_page_url')
            ->where('completedActivities.current_page', 1)
            ->where('completedActivities.per_page', 5)
        );

    actingAs($student)
        ->get("/student/courses/{$course->id}?tab=completed&page=2")
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/courses/show')
            ->has('completedActivities.data', 5)
            ->where('completedActivities.current_page', 2)
        );
});
