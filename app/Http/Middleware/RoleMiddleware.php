<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * A simple role-based access middleware.
 *
 * This middleware checks the currently authenticated user for a given role.
 * If the user does not possess the required role, they are redirected
 * back to the home page with an error message. The middleware works
 * alongside the Spatie permission package but will also fall back to
 * checking the `role` column on the user model for convenience.
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Check via Spatie roles if available
        if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
            return $next($request);
        }

        // Fallback: check the role attribute
        if ($user->role === $role) {
            return $next($request);
        }

        // Default: deny access
        return redirect('/')->with('error', 'You are not authorised to access this page.');
    }
}