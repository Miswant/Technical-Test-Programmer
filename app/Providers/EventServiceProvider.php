<?php

namespace App\Providers;

use App\Events\ProjectStatusChanged;
use App\Listeners\SendProjectStatusChangedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ProjectStatusChanged::class => [
            SendProjectStatusChangedNotification::class,
        ],
    ];
}
