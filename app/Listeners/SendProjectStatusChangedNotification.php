<?php

namespace App\Listeners;

use App\Events\ProjectStatusChanged;
use App\Notifications\ProjectStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendProjectStatusChangedNotification implements ShouldQueue
{
    public function handle(ProjectStatusChanged $event): void
    {
        $event->project->loadMissing('user');

        $event->project->user->notify(new ProjectStatusChangedNotification(
            $event->project,
            $event->newStatus,
            $event->remarks
        ));
    }
}
