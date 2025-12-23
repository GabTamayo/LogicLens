<?php

use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Course;
use App\Models\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => RoleName::TEACHER->value]);
    Role::create(['name' => RoleName::STUDENT->value]);

    $this->teacher = User::factory()->create();
    $this->teacher->assignRole(RoleName::TEACHER->value);

    $this->student = User::factory()->create();
    $this->student->assignRole(RoleName::STUDENT->value);

    $this->course = Course::factory()->for($this->teacher, 'user')->create();
    $this->activity = Activity::factory()->for($this->teacher, 'user')->create();
    $this->activityLink = ActivityLink::factory()
        ->for($this->activity)
        ->for($this->course)
        ->create(['is_open' => true]);

    // Enroll student in course
    $this->course->students()->attach($this->student->id);
});

it('calculates score correctly when all test cases pass', function () {
    $testCase1 = TestCase::factory()->for($this->activity)->create(['score' => 10]);
    $testCase2 = TestCase::factory()->for($this->activity)->create(['score' => 15]);

    actingAs($this->student);

    $response = postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
        'test_results' => [
            ['test_case_id' => $testCase1->id, 'passed' => true],
            ['test_case_id' => $testCase2->id, 'passed' => true],
        ],
    ]);

    $response->assertRedirect();

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();
    expect($submission->score)->toBe('25.00');
    expect($submission->total_score)->toBe('25.00'); // Computed from test cases
});

it('calculates score correctly when some test cases fail', function () {
    $testCase1 = TestCase::factory()->for($this->activity)->create(['score' => 10]);
    $testCase2 = TestCase::factory()->for($this->activity)->create(['score' => 15]);

    actingAs($this->student);

    $response = postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
        'test_results' => [
            ['test_case_id' => $testCase1->id, 'passed' => true],
            ['test_case_id' => $testCase2->id, 'passed' => false],
        ],
    ]);

    $response->assertRedirect();

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();
    expect($submission->score)->toBe('10.00');
    expect($submission->total_score)->toBe('25.00'); // Computed from test cases
});

it('calculates score as zero when no test results are provided', function () {
    TestCase::factory()->for($this->activity)->create(['score' => 10]);
    TestCase::factory()->for($this->activity)->create(['score' => 15]);

    actingAs($this->student);

    $response = postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
    ]);

    $response->assertRedirect();

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();
    expect($submission->score)->toBe('0.00');
    expect($submission->total_score)->toBe('25.00'); // Computed from test cases
});

it('includes score fields in submission record', function () {
    $testCase = TestCase::factory()->for($this->activity)->create(['score' => 20]);

    actingAs($this->student);

    postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
        'test_results' => [
            ['test_case_id' => $testCase->id, 'passed' => true],
        ],
    ]);

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();

    expect($submission)->not->toBeNull();
    expect($submission->score)->toBe('20.00');
    expect($submission->total_score)->toBe('20.00'); // Computed from test cases
});
