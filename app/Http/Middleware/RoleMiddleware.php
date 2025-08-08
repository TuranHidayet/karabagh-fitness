<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check() || !auth()->user()->hasRole($role)) {
            return response()->json(['message' => 'İcazəniz yoxdur'], 403);
        }

        return $next($request);
    }
}

