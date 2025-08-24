<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogFailedLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(\Illuminate\Auth\Events\Failed $event): void
    {
        \Log::warning('AUTH FAILED', [
            'email' => $event->credentials['email'] ?? null,
            'guard' => $event->guard,
        ]);
    }
}
