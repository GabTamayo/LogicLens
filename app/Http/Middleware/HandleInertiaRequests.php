<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $quoteString = Inspiring::quote();
        $user = $request->user();

        $shared = [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    // Helpful flags for the frontend
                    'is_student' => $user->hasRole(RoleName::STUDENT->value),
                ] : null,
            ],

            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];

        if (
            ! $user &&
            (
                $request->is('login') ||
                $request->is('register') ||
                $request->is('forgot-password') ||
                $request->is('reset-password/*')
            )
        ) {
            // Parse the new Inspiring format: “ Quote text ” — Author
            $quote = [
                'message' => $quoteString,
                'author' => null,
                'image' => asset('images/clonewave-bg.jpg'),
            ];

            if (preg_match('/“(.+)”\s+—\s+(.+)/s', strip_tags($quoteString), $matches)) {
                $quote['message'] = trim($matches[1]);
                $quote['author'] = trim($matches[2]);
            }

            $shared['name'] = config('app.name');
            $shared['quote'] = $quote;
        }

        return $shared;
    }
}
