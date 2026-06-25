<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle(
        $request,
        Closure $next,
        ...$roles
    ) {
        if (!auth()->check()) {
            abort(403);
        }

        $role = auth()->user()->role;

        // Author-only scopes must stay isolated from non-author users, including superadmin.
        if (in_array('author', $roles, true) && !in_array($role, $roles, true)) {
            abort(403);
        }

        if (auth()->user()->role === 'superadmin') {
            return $next($request);
        }

        if (
            !in_array(
                $role,
                $roles
            )
        ) {

            abort(403);

        }

        return $next(
            $request
        );
    }
}