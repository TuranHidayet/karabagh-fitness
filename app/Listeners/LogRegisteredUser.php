<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogRegisteredUser
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
    public function handle(\Illuminate\Auth\Events\Registered $event): void
    {
        \Log::info('AUTH REGISTERED', [
            'user_id' => $event->user->id ?? null,
            'email'   => $event->user->email ?? null,
        ]);
    }
}
