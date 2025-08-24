<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(\App\Models\User $user): void
    {
        \Log::info('USER CREATED', ['id'=>$user->id, 'email'=>$user->email]);
    }
    public function updated(\App\Models\User $user): void
    {
        \Log::info('USER UPDATED', [
            'id'=>$user->id,
            'changes'=>$user->getChanges(),
        ]);
    }
    public function deleted(\App\Models\User $user): void
    {
        \Log::warning('USER DELETED', ['id'=>$user->id, 'email'=>$user->email]);
    }


    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
