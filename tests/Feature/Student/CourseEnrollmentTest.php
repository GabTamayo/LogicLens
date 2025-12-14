<?php

use App\Enums\RoleName;
use App\Models\Course;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

beforeEach(function () {
    Role::create(['name' => RoleName::TEACHER->value]);
    Role::create(['name' => RoleName::STUDENT->value]);
});

it('allows student to enroll in active course with valid access code', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
        'access_code' => 'ABC-123-XYZ',
        'is_active' => true,
    ]);

    actingAs($student)
        ->post('/student/enroll', [
            'access_code' => 'ABC-123-XYZ',
        ])
        ->assertRedirect('/student/courses')
        ->assertSessionHas('success', 'Successfully enrolled in the course!');

    assertDatabaseHas('course_user', [
        'course_id' => $course->id,
        'user_id' => $student->id,
    ]);

    expect($student->enrolledCourses)->toHaveCount(1);
    expect($student->enrolledCourses->first()->id)->toBe($course->id);
});

it('prevents student from enrolling in inactive course', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->inactive()->create([
        'user_id' => $teacher->id,
        'access_code' => 'ABC-123-XYZ',
    ]);

    actingAs($student)
        ->post('/student/enroll', [
            'access_code' => 'ABC-123-XYZ',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors([
            'access_code' => 'This course is not currently active.',
        ]);

    expect($student->enrolledCourses)->toHaveCount(0);
});

it('prevents student from enrolling with invalid access code', function () {
    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    actingAs($student)
        ->post('/student/enroll', [
            'access_code' => 'INVALID-CODE',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors([
            'access_code' => 'Invalid access code. Please check and try again.',
        ]);

    expect($student->enrolledCourses)->toHaveCount(0);
});

it('prevents student from enrolling in same course twice', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
        'access_code' => 'ABC-123-XYZ',
        'is_active' => true,
    ]);

    $course->students()->attach($student->id, [
        'enrolled_at' => now(),
    ]);

    actingAs($student)
        ->post('/student/enroll', [
            'access_code' => 'ABC-123-XYZ',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors([
            'access_code' => 'You are already enrolled in this course.',
        ]);

    expect($student->enrolledCourses)->toHaveCount(1);
});

it('requires access code field', function () {
    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    actingAs($student)
        ->post('/student/enroll', [
            'access_code' => '',
        ])
        ->assertRedirect()
        ->assertSessionHasErrors([
            'access_code' => 'Please enter an access code.',
        ]);
});

it('prevents unauthenticated users from enrolling', function () {
    post('/student/enroll', [
        'access_code' => 'ABC-123-XYZ',
    ])->assertRedirect('/login');
});

it('prevents non-student users from accessing enrollment', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    actingAs($teacher)
        ->post('/student/enroll', [
            'access_code' => 'ABC-123-XYZ',
        ])
        ->assertForbidden();
});

it('displays enrolled courses for student', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course1 = Course::factory()->create([
        'user_id' => $teacher->id,
        'name' => 'Introduction to Laravel',
    ]);

    $course2 = Course::factory()->create([
        'user_id' => $teacher->id,
        'name' => 'Advanced Vue.js',
    ]);

    $course1->students()->attach($student->id, ['enrolled_at' => now()]);
    $course2->students()->attach($student->id, ['enrolled_at' => now()]);

    actingAs($student)
        ->get('/student/courses')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Student/Courses')
            ->has('enrolledCourses', 2)
            ->where('enrolledCourses.0.name', 'Advanced Vue.js')
            ->where('enrolledCourses.1.name', 'Introduction to Laravel')
        );
});

it('stores enrolled_at timestamp on enrollment', function () {
    $teacher = User::factory()->create();
    $teacher->assignRole(RoleName::TEACHER->value);

    $student = User::factory()->create();
    $student->assignRole(RoleName::STUDENT->value);

    $course = Course::factory()->create([
        'user_id' => $teacher->id,
        'access_code' => 'ABC-123-XYZ',
        'is_active' => true,
    ]);

    actingAs($student)
        ->post('/student/enroll', [
            'access_code' => 'ABC-123-XYZ',
        ]);

    $enrollment = $student->enrolledCourses()->first();

    expect($enrollment->pivot->enrolled_at)->not->toBeNull();
    expect($enrollment->pivot->enrolled_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});
