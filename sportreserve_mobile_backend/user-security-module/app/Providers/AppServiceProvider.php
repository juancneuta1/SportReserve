<?php

namespace App\Providers;

use App\Events\UserLoginFailed;
use App\Events\UserLoginSucceeded;
use App\Listeners\RecordFailedUserLogin;
use App\Listeners\RecordUserAccessLog;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Event::listen(UserLoginSucceeded::class, [RecordUserAccessLog::class, 'handle']);
        Event::listen(UserLoginFailed::class, [RecordFailedUserLogin::class, 'handle']);
    }
}
