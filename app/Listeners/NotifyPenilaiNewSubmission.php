<?php

namespace App\Listeners;

use App\Enums\ProjectStatus;
use App\Events\ProjectStatusChanged;
use App\Models\User;
use App\Notifications\ProjectStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyPenilaiNewSubmission implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 30;

    public function handle(ProjectStatusChanged $event): void
    {
        if (!in_array($event->newStatus, [ProjectStatus::SUBMITTED, ProjectStatus::REVISED])) {
            return;
        }

        User::role('penilai')
            ->select(['id', 'name', 'email'])
            ->chunkById(200, function ($penilaiList) use ($event) {
                foreach ($penilaiList as $penilai) {
                    $penilai->notify(new ProjectStatusChangedNotification(
                        $event->project,
                        $event->newStatus,
                        $event->remarks
                    ));
                }
            });
    }

    public function failed(ProjectStatusChanged $event, \Throwable $exception): void
    {
        Log::error("Gagal mengirim notifikasi submission project {$event->project->project_code} ke penilai: {$exception->getMessage()}");
    }
}
