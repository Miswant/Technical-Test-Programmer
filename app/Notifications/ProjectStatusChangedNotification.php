<?php

namespace App\Notifications;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Project $project,
        public ProjectStatus $status,
        public ?string $remarks = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Status Permohonan Berubah')
            ->line("Permohonan {$this->project->project_code} berubah menjadi {$this->status->value}.")
            ->when($this->remarks, fn (MailMessage $message) => $message->line("Catatan: {$this->remarks}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'project_id' => $this->project->id,
            'project_code' => $this->project->project_code,
            'status' => $this->status->value,
            'remarks' => $this->remarks,
        ];
    }
}
