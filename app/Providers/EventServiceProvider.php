<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogLogout;
use App\Listeners\LogRegisteredUser;
use App\Listeners\LogFailedLogin;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogSuccessfulLogin::class,
        ],
        Logout::class => [
            LogLogout::class,
        ],
        Registered::class => [
            LogRegisteredUser::class,
        ],
        Failed::class => [
            LogFailedLogin::class,
        ],
    ];
}
