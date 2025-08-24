<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestResponseLogger
{
    public function handle(Request $request, Closure $next)
    {
        // Sensitive field-ləri çıxart
        $masked = collect($request->all())->except([
            'password','password_confirmation','current_password','token'
        ]);

        Log::info('HTTP REQUEST', [
            'method' => $request->method(),
            'path'   => $request->path(),
            'ip'     => $request->ip(),
            'user_id'=> optional($request->user())->id,
            'payload'=> $masked,
            'ua'     => substr((string) $request->userAgent(), 0, 255),
        ]);

        $response = $next($request);

        Log::info('HTTP RESPONSE', [
            'status' => $response->getStatusCode(),
            'path'   => $request->path(),
            'user_id'=> optional($request->user())->id,
        ]);

        return $response;
    }
}
