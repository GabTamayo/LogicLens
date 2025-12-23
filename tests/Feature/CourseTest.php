<?php

use App\Models\Course;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('has fillable attributes', function () {
    $course = new Course;

    expect($course->getFillable())->toBe(['name', 'access_code', 'is_active']);
});

it('can be created with valid attributes', function () {
    $user = User::factory()->create();

    $course = Course::factory()->create([
        'user_id' => $user->id,
        'name' => 'CS101-A',
        'access_code' => 'ABC123',
        'is_active' => true,
    ]);

    expect($course->name)->toBe('CS101-A');
    expect($course->access_code)->toBe('ABC123');
    expect($course->is_active)->toBeTrue();
    expect($course->user_id)->toBe($user->id);
    expect($course->exists)->toBeTrue();
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);

    expect($course->user)->toBeInstanceOf(User::class);
    expect($course->user->id)->toBe($user->id);
});

test('factory creates course with user relationship', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);

    expect($course->user->id)->toBe($user->id);
    expect($user->courses)->toHaveCount(1);
    expect($user->courses->first()->id)->toBe($course->id);
});

test('user can have multiple courses', function () {
    $user = User::factory()->create();

    $course1 = Course::factory()->create(['user_id' => $user->id]);
    $course2 = Course::factory()->create(['user_id' => $user->id]);

    expect($user->courses)->toHaveCount(2);
    expect($user->courses->pluck('id'))->toContain($course1->id, $course2->id);
});

test('authenticated user can view courses index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/courses');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Courses/Index'));
});

test('guest cannot view courses index page', function () {
    $response = $this->get('/courses');

    $response->assertRedirect('/login');
});

test('authenticated user can create a course', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/courses', [
        'name' => 'Grade 10 Math',
        'access_code' => 'MATH10A',
        'is_active' => true,
    ]);

    $response->assertRedirect('/courses');
    $this->assertDatabaseHas('courses', [
        'name' => 'Grade 10 Math',
        'access_code' => 'MATH10A',
        'is_active' => true,
        'user_id' => $user->id,
    ]);
});

test('guest cannot create a course', function () {
    $response = $this->post('/courses', [
        'name' => 'Grade 10 Math',
        'access_code' => 'MATH10A',
        'is_active' => true,
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseCount('courses', 0);
});

test('course name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/courses', [
        'access_code' => 'MATH10A',
        'is_active' => true,
    ]);

    $response->assertInvalid(['name']);
});

test('course access code is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/courses', [
        'name' => 'Grade 10 Math',
        'is_active' => true,
    ]);

    $response->assertInvalid(['access_code']);
});

test('course access code must be unique', function () {
    $user = User::factory()->create();
    Course::factory()->create(['access_code' => 'DUPLICATE']);

    $response = $this->actingAs($user)->post('/courses', [
        'name' => 'Grade 10 Math',
        'access_code' => 'DUPLICATE',
        'is_active' => true,
    ]);

    $response->assertInvalid(['access_code']);
});

test('user only sees their own courses', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $course1 = Course::factory()->create(['user_id' => $user1->id, 'name' => 'User 1 Course']);
    $course2 = Course::factory()->create(['user_id' => $user2->id, 'name' => 'User 2 Course']);

    $response = $this->actingAs($user1)->get('/courses');

    $response->assertInertia(fn ($page) => $page
        ->component('Courses/Index')
        ->has('courses.data', 1)
        ->where('courses.data.0.id', $course1->id)
    );
});

test('courses can be searched by name', function () {
    $user = User::factory()->create();

    Course::factory()->create(['user_id' => $user->id, 'name' => 'Math Course']);
    Course::factory()->create(['user_id' => $user->id, 'name' => 'Science Course']);

    $response = $this->actingAs($user)->get('/courses?search=Math');

    $response->assertInertia(fn ($page) => $page
        ->component('Courses/Index')
        ->has('courses.data', 1)
        ->where('courses.data.0.name', 'Math Course')
    );
});

test('courses can be searched by access code', function () {
    $user = User::factory()->create();

    Course::factory()->create(['user_id' => $user->id, 'access_code' => 'MATH101']);
    Course::factory()->create(['user_id' => $user->id, 'access_code' => 'SCI101']);

    $response = $this->actingAs($user)->get('/courses?search=MATH');

    $response->assertInertia(fn ($page) => $page
        ->component('Courses/Index')
        ->has('courses.data', 1)
        ->where('courses.data.0.access_code', 'MATH101')
    );
});

test('courses can be sorted by name ascending', function () {
    $user = User::factory()->create();

    Course::factory()->create(['user_id' => $user->id, 'name' => 'Zebra']);
    Course::factory()->create(['user_id' => $user->id, 'name' => 'Apple']);

    $response = $this->actingAs($user)->get('/courses?sort=name_asc');

    $response->assertInertia(fn ($page) => $page
        ->component('Courses/Index')
        ->where('courses.data.0.name', 'Apple')
        ->where('courses.data.1.name', 'Zebra')
    );
});

test('courses can be sorted by name descending', function () {
    $user = User::factory()->create();

    Course::factory()->create(['user_id' => $user->id, 'name' => 'Apple']);
    Course::factory()->create(['user_id' => $user->id, 'name' => 'Zebra']);

    $response = $this->actingAs($user)->get('/courses?sort=name_desc');

    $response->assertInertia(fn ($page) => $page
        ->component('Courses/Index')
        ->where('courses.data.0.name', 'Zebra')
        ->where('courses.data.1.name', 'Apple')
    );
});

test('courses are paginated', function () {
    $user = User::factory()->create();

    Course::factory()->count(15)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/courses');

    $response->assertInertia(fn ($page) => $page
        ->component('Courses/Index')
        ->has('courses.data', 9)
        ->where('courses.per_page', 9)
        ->where('courses.total', 15)
    );
});

test('authenticated user can delete their own course', function () {
    $user = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->delete("/courses/{$course->id}");

    $response->assertRedirect('/courses');
    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
});

test('user cannot delete another users course', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $course = Course::factory()->create(['user_id' => $user1->id]);

    $response = $this->actingAs($user2)->delete("/courses/{$course->id}");

    $response->assertNotFound();
    $this->assertDatabaseHas('courses', ['id' => $course->id]);
});

test('guest cannot delete a course', function () {
    $course = Course::factory()->create();

    $response = $this->delete("/courses/{$course->id}");

    $response->assertRedirect('/login');
    $this->assertDatabaseHas('courses', ['id' => $course->id]);
});
