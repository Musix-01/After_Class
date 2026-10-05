<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /** A suspended account is signed out on its very next request. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isSuspended()) {
            $message = 'Your account is suspended'
                . ($user->suspended_until ? ' until ' . $user->suspended_until->format('M j, Y g:i A') : '')
                . '.'
                . ($user->suspension_reason ? ' Reason: ' . $user->suspension_reason : '');

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')
                ->with('status', $message)
                ->withErrors(['email' => $message]);
        }

        return $next($request);
    }
}
