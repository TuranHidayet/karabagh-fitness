<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\LogEntry;

class RequestResponseLogger
{
    public function handle(Request $request, Closure $next)
    {
        $masked = collect($request->all())->except([
            'password','password_confirmation','current_password','token'
        ]);

        try {
            LogEntry::create([
            'message'    => 'HTTP REQUEST',
            'level'      => 'info',
            'channel'    => 'http',
            'method'     => $request->method(),
            'path'       => $request->path(),
            'ip'         => $request->ip(),
            'user_id'    => optional($request->user())->id,
            'payload'    => $masked,
            'ua'         => substr((string) $request->userAgent(), 0, 255),
            'created_at' => now(),
        ]);
        } catch (\Exception $e) {
            \Log::error("Log DB-yə yazılmadı: ".$e->getMessage());
        }

        

        $response = $next($request);

        LogEntry::create([
            'message' => 'HTTP RESPONSE',
            'level'   => 'info',
            'method'  => $request->method(),
            'path'    => $request->path(),
            'ip'      => $request->ip(),
            'user_id' => optional($request->user())->id,
            'payload' => [
                'status' => $response->getStatusCode(),
                'body'   => method_exists($response, 'getContent') ? substr($response->getContent(), 0, 500) : null
            ],
            'ua'      => substr((string) $request->userAgent(), 0, 255),
            'created_at' => now(),
        ]);

        return $response;
    }
}
