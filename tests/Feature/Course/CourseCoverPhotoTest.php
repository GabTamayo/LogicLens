<?php

use App\Enums\RoleName;
use App\Models\Course;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can create course with cover photo', function () {
    Role::create(['name' => RoleName::TEACHER->value]);
    $user = User::factory()->create();
    $user->assignRole(RoleName::TEACHER->value);

    actingAs($user)
        ->post('/courses', [
            'name' => 'Test Course',
            'access_code' => 'TEST1234',
            'cover_photo' => 'cover-1.jpg',
            'is_active' => true,
        ])
        ->assertRedirect('/courses');

    $this->assertDatabaseHas('courses', [
        'name' => 'Test Course',
        'access_code' => 'TEST1234',
        'cover_photo' => 'cover-1.jpg',
    ]);
});

test('can update course cover photo', function () {
    Role::create(['name' => RoleName::TEACHER->value]);
    $user = User::factory()->create();
    $user->assignRole(RoleName::TEACHER->value);
    $course = Course::factory()->create([
        'user_id' => $user->id,
        'cover_photo' => 'cover-1.jpg',
    ]);

    actingAs($user)
        ->put("/courses/{$course->id}", [
            'name' => $course->name,
            'access_code' => $course->access_code,
            'cover_photo' => 'cover-2.jpg',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('courses', [
        'id' => $course->id,
        'cover_photo' => 'cover-2.jpg',
    ]);
});

test('course defaults to cover-5.jpg when no cover photo specified', function () {
    Role::create(['name' => RoleName::TEACHER->value]);
    $user = User::factory()->create();
    $user->assignRole(RoleName::TEACHER->value);

    actingAs($user)
        ->post('/courses', [
            'name' => 'Test Course',
            'access_code' => 'TEST1234',
            'is_active' => true,
        ])
        ->assertRedirect('/courses');

    $course = Course::where('access_code', 'TEST1234')->first();
    expect($course->cover_photo)->toBe('cover-5.jpg');
});

test('validates cover photo is one of available options', function () {
    Role::create(['name' => RoleName::TEACHER->value]);
    $user = User::factory()->create();
    $user->assignRole(RoleName::TEACHER->value);

    actingAs($user)
        ->post('/courses', [
            'name' => 'Test Course',
            'access_code' => 'TEST1234',
            'cover_photo' => 'invalid-cover.jpg',
            'is_active' => true,
        ])
        ->assertSessionHasErrors('cover_photo');
});

test('cover photo is included in course show page', function () {
    Role::create(['name' => RoleName::TEACHER->value]);
    $user = User::factory()->create();
    $user->assignRole(RoleName::TEACHER->value);
    $course = Course::factory()->create([
        'user_id' => $user->id,
        'cover_photo' => 'cover-3.jpg',
    ]);

    actingAs($user)
        ->get("/courses/{$course->id}")
        ->assertInertia(fn ($page) => $page
            ->component('Courses/Show')
            ->has('course', fn ($course) => $course
                ->where('cover_photo', 'cover-3.jpg')
                ->etc()
            )
        );
});
