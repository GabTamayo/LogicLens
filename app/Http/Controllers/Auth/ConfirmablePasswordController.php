<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password page.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        if ($request->session()->has('auth.password_confirmed_at')) {
            $confirmedAt = $request->session()->get('auth.password_confirmed_at');
            $timeout = config('auth.password_timeout', 10800); // Default 3 hours

            // If still within the timeout period, redirect away
            if (time() - $confirmedAt < $timeout) {
                return redirect()->intended(route('dashboard', absolute: false));
            }
        }

        return Inertia::render('auth/ConfirmPassword');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        inertia()->clearHistory();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
