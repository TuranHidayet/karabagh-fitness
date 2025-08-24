<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
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
    public function handle(\Illuminate\Auth\Events\Login $event): void
    {
        \Log::info('AUTH LOGIN', [
            'user_id' => $event->user->id ?? null,
            'email'   => $event->user->email ?? null,
            'guard'   => $event->guard,
        ]);
    }
}
