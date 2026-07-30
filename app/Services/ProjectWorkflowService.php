<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use App\Enums\ProjectStatus;
use App\Events\ProjectStatusChanged;
use App\Models\ApplicationLog;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProjectWorkflowService
{
    /**
     * Transition a project to a new status.
     *
     * @param Project $project
     * @param User $actor
     * @param ProjectStatus $newStatus
     * @param string|null $remarks
     * @return Project
     * @throws InvalidArgumentException
     */
    public function transition(Project $project, User $actor, ProjectStatus $newStatus, ?string $remarks = null): Project
    {
        $oldStatus = $project->status;

        // Validasi transisi status
        if (!$oldStatus->canTransitionTo($newStatus)) {
            throw new InvalidArgumentException(
                "Tidak dapat mengubah status project dari {$oldStatus->value} ke {$newStatus->value}."
            );
        }

        // Catat dan update dengan database transaction
        DB::transaction(function () use ($project, $actor, $oldStatus, $newStatus, $remarks) {
            $project->update([
                'status' => $newStatus,
            ]);

            ApplicationLog::create([
                'project_id' => $project->id,
                'actor_id'   => $actor->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks'    => $remarks,
                'created_at' => now(),
            ]);
        });

        // Trigger Event / Queue Notification
        event(new ProjectStatusChanged($project, $actor, $oldStatus, $newStatus, $remarks));

        return $project;
    }
}
