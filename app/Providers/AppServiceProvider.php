<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Post;
use App\Policies\CommentPolicy;
use App\Policies\PostPolicy;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS when deployed on Render
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);

        // Security trail: Laravel fires these events no matter how your LoginController is written.
        Event::listen(Login::class, fn (Login $e) => ActivityLog::record(
            'auth.login',
            "{$e->user->name} logged in",
            $e->user,
            [],
            false,
            $e->user->id
        ));

        Event::listen(Logout::class, function (Logout $e) {
            if ($e->user) {
                ActivityLog::record(
                    'auth.logout',
                    "{$e->user->name} logged out",
                    $e->user,
                    [],
                    false,
                    $e->user->id
                );
            }
        });

        Event::listen(Failed::class, fn (Failed $e) => ActivityLog::record(
            'auth.failed',
            'Failed login attempt for ' .
                ($e->credentials['email'] ?? $e->credentials['username'] ?? 'unknown account'),
            null,
            [],
            false,
            $e->user?->id
        ));
    }
}