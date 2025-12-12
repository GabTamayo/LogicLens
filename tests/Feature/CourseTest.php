<?php

use App\Models\Section;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('has fillable attributes', function () {
    $section = new Section;

    expect($section->getFillable())->toBe(['name', 'access_code', 'is_active']);
});

it('can be created with valid attributes', function () {
    $user = User::factory()->create();

    $section = Section::factory()->create([
        'user_id' => $user->id,
        'name' => 'CS101-A',
        'access_code' => 'ABC123',
        'is_active' => true,
    ]);

    expect($section->name)->toBe('CS101-A');
    expect($section->access_code)->toBe('ABC123');
    expect($section->is_active)->toBeTrue();
    expect($section->user_id)->toBe($user->id);
    expect($section->exists)->toBeTrue();
});

it('belongs to a user', function () {
    $user = User::factory()->create();
    $section = Section::factory()->create(['user_id' => $user->id]);

    expect($section->user)->toBeInstanceOf(User::class);
    expect($section->user->id)->toBe($user->id);
});

test('factory creates section with user relationship', function () {
    $user = User::factory()->create();
    $section = Section::factory()->create(['user_id' => $user->id]);

    expect($section->user->id)->toBe($user->id);
    expect($user->sections)->toHaveCount(1);
    expect($user->sections->first()->id)->toBe($section->id);
});

test('user can have multiple sections', function () {
    $user = User::factory()->create();

    $section1 = Section::factory()->create(['user_id' => $user->id]);
    $section2 = Section::factory()->create(['user_id' => $user->id]);

    expect($user->sections)->toHaveCount(2);
    expect($user->sections->pluck('id'))->toContain($section1->id, $section2->id);
});

test('authenticated user can view sections index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/sections');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Sections/Index'));
});

test('guest cannot view sections index page', function () {
    $response = $this->get('/sections');

    $response->assertRedirect('/login');
});

test('authenticated user can create a section', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sections', [
        'name' => 'Grade 10 Math',
        'access_code' => 'MATH10A',
        'is_active' => true,
    ]);

    $response->assertRedirect('/sections');
    $this->assertDatabaseHas('sections', [
        'name' => 'Grade 10 Math',
        'access_code' => 'MATH10A',
        'is_active' => true,
        'user_id' => $user->id,
    ]);
});

test('guest cannot create a section', function () {
    $response = $this->post('/sections', [
        'name' => 'Grade 10 Math',
        'access_code' => 'MATH10A',
        'is_active' => true,
    ]);

    $response->assertRedirect('/login');
    $this->assertDatabaseCount('sections', 0);
});

test('section name is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sections', [
        'access_code' => 'MATH10A',
        'is_active' => true,
    ]);

    $response->assertInvalid(['name']);
});

test('section access code is required', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/sections', [
        'name' => 'Grade 10 Math',
        'is_active' => true,
    ]);

    $response->assertInvalid(['access_code']);
});

test('section access code must be unique', function () {
    $user = User::factory()->create();
    Section::factory()->create(['access_code' => 'DUPLICATE']);

    $response = $this->actingAs($user)->post('/sections', [
        'name' => 'Grade 10 Math',
        'access_code' => 'DUPLICATE',
        'is_active' => true,
    ]);

    $response->assertInvalid(['access_code']);
});

test('user only sees their own sections', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $section1 = Section::factory()->create(['user_id' => $user1->id, 'name' => 'User 1 Section']);
    $section2 = Section::factory()->create(['user_id' => $user2->id, 'name' => 'User 2 Section']);

    $response = $this->actingAs($user1)->get('/sections');

    $response->assertInertia(fn ($page) => $page
        ->component('Sections/Index')
        ->has('sections.data', 1)
        ->where('sections.data.0.id', $section1->id)
    );
});

test('sections can be searched by name', function () {
    $user = User::factory()->create();

    Section::factory()->create(['user_id' => $user->id, 'name' => 'Math Section']);
    Section::factory()->create(['user_id' => $user->id, 'name' => 'Science Section']);

    $response = $this->actingAs($user)->get('/sections?search=Math');

    $response->assertInertia(fn ($page) => $page
        ->component('Sections/Index')
        ->has('sections.data', 1)
        ->where('sections.data.0.name', 'Math Section')
    );
});

test('sections can be searched by access code', function () {
    $user = User::factory()->create();

    Section::factory()->create(['user_id' => $user->id, 'access_code' => 'MATH101']);
    Section::factory()->create(['user_id' => $user->id, 'access_code' => 'SCI101']);

    $response = $this->actingAs($user)->get('/sections?search=MATH');

    $response->assertInertia(fn ($page) => $page
        ->component('Sections/Index')
        ->has('sections.data', 1)
        ->where('sections.data.0.access_code', 'MATH101')
    );
});

test('sections can be sorted by name ascending', function () {
    $user = User::factory()->create();

    Section::factory()->create(['user_id' => $user->id, 'name' => 'Zebra']);
    Section::factory()->create(['user_id' => $user->id, 'name' => 'Apple']);

    $response = $this->actingAs($user)->get('/sections?sort=name_asc');

    $response->assertInertia(fn ($page) => $page
        ->component('Sections/Index')
        ->where('sections.data.0.name', 'Apple')
        ->where('sections.data.1.name', 'Zebra')
    );
});

test('sections can be sorted by name descending', function () {
    $user = User::factory()->create();

    Section::factory()->create(['user_id' => $user->id, 'name' => 'Apple']);
    Section::factory()->create(['user_id' => $user->id, 'name' => 'Zebra']);

    $response = $this->actingAs($user)->get('/sections?sort=name_desc');

    $response->assertInertia(fn ($page) => $page
        ->component('Sections/Index')
        ->where('sections.data.0.name', 'Zebra')
        ->where('sections.data.1.name', 'Apple')
    );
});

test('sections are paginated', function () {
    $user = User::factory()->create();

    Section::factory()->count(15)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->get('/sections');

    $response->assertInertia(fn ($page) => $page
        ->component('Sections/Index')
        ->has('sections.data', 9)
        ->where('sections.per_page', 9)
        ->where('sections.total', 15)
    );
});
