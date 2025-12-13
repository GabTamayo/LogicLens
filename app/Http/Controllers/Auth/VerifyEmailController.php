<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        $redirectRoute = $user->hasRole(RoleName::STUDENT->value)
            ? route('student.courses.index', absolute: false)
            : route('dashboard', absolute: false);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended($redirectRoute.'?verified=1');
        }

        $request->fulfill();

        $user->forceFill([
            'verification_token' => null,
        ])->save();

        inertia()->clearHistory();

        return redirect()->intended($redirectRoute.'?verified=1');
    }
}
