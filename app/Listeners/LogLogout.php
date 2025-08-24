<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogLogout
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
    public function handle(\Illuminate\Auth\Events\Logout $event): void
    {
        \Log::info('AUTH LOGOUT', [
            'user_id' => $event->user->id ?? null,
            'email'   => $event->user->email ?? null,
            'guard'   => $event->guard,
        ]);
    }
}
