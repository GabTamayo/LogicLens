<?php

use App\Enums\RoleName;
use App\Models\User;
use App\Notifications\Auth\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('email verification notification is queued when user registers', function () {
    Queue::fake();
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => RoleName::STUDENT->value,
    ]);

    $user = User::where('email', 'test@example.com')->first();

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('verification notification implements ShouldQueue', function () {
    $notification = new VerifyEmail;

    expect($notification)->toBeInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class);
});

test('user has verification token after registration', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => RoleName::TEACHER->value,
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user->verification_token)->not->toBeNull();
});
