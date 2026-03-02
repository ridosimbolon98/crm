<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403);
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses fitur ini.');
        }

        return $next($request);
    }
}
