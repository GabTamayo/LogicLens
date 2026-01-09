<?php

use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;

beforeEach(function () {
    $this->teacher = User::factory()->create();
    $this->teacher->assignRole(RoleName::TEACHER->value);

    $this->student = User::factory()->create();
    $this->student->assignRole(RoleName::STUDENT->value);

    $this->course = Course::factory()->create(['user_id' => $this->teacher->id]);
    $this->course->students()->attach($this->student->id, ['enrolled_at' => now()]);

    $this->activity = Activity::factory()->create([
        'user_id' => $this->teacher->id,
        'language' => 'python',
    ]);

    $this->activityLink = ActivityLink::factory()->create([
        'course_id' => $this->course->id,
        'activity_id' => $this->activity->id,
        'is_open' => true,
    ]);
});

test('submission record is created when student navigates to submission page', function () {
    $this->actingAs($this->student);

    // Initially no submission exists
    expect(Submission::where('user_id', $this->student->id)->count())->toBe(0);

    // Navigate to submission page
    $response = $this->get("/student/submit/{$this->activityLink->token}");

    $response->assertOk();

    // Submission record should now exist
    $submission = Submission::where('user_id', $this->student->id)
        ->where('activity_link_id', $this->activityLink->id)
        ->first();

    expect($submission)->not->toBeNull();
    expect($submission->submitted_at)->toBeNull(); // Not submitted yet
    expect($submission->code_content)->toBeNull(); // No code yet
    expect($submission->language)->toBe('python');
});

test('draft is auto-saved to database', function () {
    $this->actingAs($this->student);

    // Create initial submission
    $submission = Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => $this->student->id,
        'language' => 'python',
        'submitted_at' => null,
    ]);

    // Save draft
    $response = $this->post("/student/submission/{$this->activityLink->token}/draft", [
        'code' => 'print("Hello World")',
        'stdin' => 'test input',
    ]);

    $response->assertStatus(200);

    // Check draft was saved
    $submission->refresh();
    expect($submission->draft_code)->toBe('print("Hello World")');
    expect($submission->draft_stdin)->toBe('test input');
    expect($submission->draft_saved_at)->not->toBeNull();
});

test('draft is loaded when student returns to submission page', function () {
    $this->actingAs($this->student);

    // Create submission with draft
    Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => $this->student->id,
        'language' => 'python',
        'draft_code' => 'print("Saved Draft")',
        'draft_stdin' => 'saved input',
        'draft_saved_at' => now(),
        'submitted_at' => null,
    ]);

    // Navigate to submission page
    $response = $this->get("/student/submit/{$this->activityLink->token}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Submissions/Create')
        ->has('draftCode')
        ->where('draftCode', 'print("Saved Draft")')
        ->where('draftStdin', 'saved input')
    );
});

test('submission sets submitted_at timestamp when submitted', function () {
    $this->actingAs($this->student);

    // Create initial submission
    $submission = Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => $this->student->id,
        'language' => 'python',
        'submitted_at' => null,
    ]);

    // Submit code
    $response = $this->post("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'print("Submitted Code")',
    ]);

    $response->assertRedirect();

    // Check submission was updated with submitted_at
    $submission->refresh();
    expect($submission->code_content)->toBe('print("Submitted Code")');
    expect($submission->submitted_at)->not->toBeNull();
});

test('only submitted submissions are counted', function () {
    $this->actingAs($this->teacher);

    // Create 3 submissions: 2 submitted, 1 draft
    Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => User::factory()->student()->create()->id,
        'language' => 'python',
        'code_content' => 'code 1',
        'submitted_at' => now(),
    ]);

    Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => User::factory()->student()->create()->id,
        'language' => 'python',
        'code_content' => 'code 2',
        'submitted_at' => now(),
    ]);

    Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => User::factory()->student()->create()->id,
        'language' => 'python',
        'draft_code' => 'draft code',
        'submitted_at' => null, // Not submitted
    ]);

    // Check count
    $count = $this->activityLink->submissions()->whereNotNull('submitted_at')->count();
    expect($count)->toBe(2);
});

test('student cannot submit twice', function () {
    $this->actingAs($this->student);

    // Create already submitted submission
    Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => $this->student->id,
        'language' => 'python',
        'code_content' => 'already submitted',
        'submitted_at' => now(),
    ]);

    // Try to submit again
    $response = $this->post("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'trying to submit again',
    ]);

    $response->assertSessionHasErrors('code_content');
});

test('teacher can also save drafts when testing', function () {
    $this->actingAs($this->teacher);

    // Create initial submission for teacher
    $submission = Submission::factory()->create([
        'activity_link_id' => $this->activityLink->id,
        'user_id' => $this->teacher->id,
        'language' => 'python',
        'submitted_at' => null,
    ]);

    // Save draft as teacher
    $response = $this->post("/student/submission/{$this->activityLink->token}/draft", [
        'code' => 'print("Teacher testing")',
        'stdin' => '',
    ]);

    $response->assertStatus(200);

    // Check draft was saved
    $submission->refresh();
    expect($submission->draft_code)->toBe('print("Teacher testing")');
});
