<?php

use App\Enums\RoleName;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Course;
use App\Models\TestCase;
use App\Models\User;
use App\Services\CodeExecutionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;
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
    TestCase::factory()->for($this->activity)->create([
        'score' => 10,
        'input' => '',
        'output' => 'Hello',
    ]);
    TestCase::factory()->for($this->activity)->create([
        'score' => 15,
        'input' => '',
        'output' => 'Hello',
    ]);

    // Mock CodeExecutionService to simulate passing test cases
    mock(CodeExecutionService::class)
        ->shouldReceive('execute')
        ->twice()
        ->andReturn([
            'compile' => ['code' => 0],
            'run' => [
                'stdout' => 'Hello',
                'stderr' => '',
                'code' => 0,
            ],
        ]);

    actingAs($this->student);

    $response = postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
    ]);

    $response->assertRedirect();

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();
    expect($submission->score)->toBe('25.00');
    expect($submission->total_score)->toBe('25.00');
});

it('calculates score correctly when some test cases fail', function () {
    TestCase::factory()->for($this->activity)->create([
        'score' => 10,
        'input' => '',
        'output' => 'Hello',
    ]);
    TestCase::factory()->for($this->activity)->create([
        'score' => 15,
        'input' => '',
        'output' => 'World',
    ]);

    // Mock CodeExecutionService: first test passes, second fails
    mock(CodeExecutionService::class)
        ->shouldReceive('execute')
        ->twice()
        ->andReturn(
            [
                'compile' => ['code' => 0],
                'run' => [
                    'stdout' => 'Hello',
                    'stderr' => '',
                    'code' => 0,
                ],
            ],
            [
                'compile' => ['code' => 0],
                'run' => [
                    'stdout' => 'Wrong Output',
                    'stderr' => '',
                    'code' => 0,
                ],
            ]
        );

    actingAs($this->student);

    $response = postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
    ]);

    $response->assertRedirect();

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();
    expect($submission->score)->toBe('10.00');
    expect($submission->total_score)->toBe('25.00');
});

it('calculates score as zero when all test cases fail', function () {
    TestCase::factory()->for($this->activity)->create([
        'score' => 10,
        'input' => '',
        'output' => 'Expected',
    ]);
    TestCase::factory()->for($this->activity)->create([
        'score' => 15,
        'input' => '',
        'output' => 'Expected',
    ]);

    // Mock CodeExecutionService: all tests fail
    mock(CodeExecutionService::class)
        ->shouldReceive('execute')
        ->twice()
        ->andReturn([
            'compile' => ['code' => 0],
            'run' => [
                'stdout' => 'Wrong',
                'stderr' => '',
                'code' => 0,
            ],
        ]);

    actingAs($this->student);

    $response = postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
    ]);

    $response->assertRedirect();

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();
    expect($submission->score)->toBe('0.00');
    expect($submission->total_score)->toBe('25.00');
});

it('includes score fields in submission record', function () {
    TestCase::factory()->for($this->activity)->create([
        'score' => 20,
        'input' => '',
        'output' => 'Hello',
    ]);

    // Mock CodeExecutionService to simulate passing test case
    mock(CodeExecutionService::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn([
            'compile' => ['code' => 0],
            'run' => [
                'stdout' => 'Hello',
                'stderr' => '',
                'code' => 0,
            ],
        ]);

    actingAs($this->student);

    postJson("/student/submit/{$this->activityLink->token}", [
        'code_content' => 'public static void main(String[] args) { System.out.println("Hello"); }',
    ]);

    $submission = $this->activityLink->submissions()->with('activityLink.activity.testCases')->first();

    expect($submission)->not->toBeNull();
    expect($submission->score)->toBe('20.00');
    expect($submission->total_score)->toBe('20.00');
});
