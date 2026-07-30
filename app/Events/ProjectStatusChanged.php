<?php

namespace App\Events;

use App\Models\Project;
use App\Models\User;
use App\Enums\ProjectStatus;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project,
        public User $actor,
        public ?ProjectStatus $oldStatus,
        public ProjectStatus $newStatus,
        public ?string $remarks = null
    ) {}
}
