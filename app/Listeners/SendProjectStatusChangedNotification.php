<?php

namespace App\Listeners;

use App\Events\ProjectStatusChanged;
use App\Notifications\ProjectStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendProjectStatusChangedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 30;

    public function handle(ProjectStatusChanged $event): void
    {
        $event->project->loadMissing('user');

        $event->project->user->notify(new ProjectStatusChangedNotification(
            $event->project,
            $event->newStatus,
            $event->remarks
        ));
    }

    public function failed(ProjectStatusChanged $event, \Throwable $exception): void
    {
        Log::error("Gagal mengirim notifikasi status ke pemohon {$event->project->project_code}: {$exception->getMessage()}");
    }
}
