<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\UserLoginSucceeded::class => [
            \App\Listeners\SendUserLoginNotification::class,
        ],
        \App\Events\AdminLoginSucceeded::class => [
            \App\Listeners\RecordAdminAccessLog::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
