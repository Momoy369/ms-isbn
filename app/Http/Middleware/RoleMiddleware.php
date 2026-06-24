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
        if (
            !in_array(
                auth()->user()->role,
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