<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Events\ProjectStatusChanged;
use App\Exceptions\WorkflowException;
use App\Models\ApplicationLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectWorkflowService
{
    public function transition(Project $project, User $actor, ProjectStatus $newStatus, ?string $remarks = null): Project
    {
        $oldStatus = $project->status;

        if (!$oldStatus->canTransitionTo($newStatus)) {
            throw WorkflowException::invalidTransition($oldStatus->value, $newStatus->value);
        }

        $allowedRoles = $newStatus->allowedRoles();
        if (!empty($allowedRoles)) {
            $hasValidRole = false;

            foreach ($allowedRoles as $role) {
                if ($actor->hasRole($role)) {
                    $hasValidRole = true;
                    break;
                }
            }

            if (!$hasValidRole) {
                $primaryRole = $actor->roles->first()?->name ?? 'tanpa-role';
                throw WorkflowException::unauthorizedRole($primaryRole, $newStatus->value);
            }
        }

        if ($actor->hasRole('pemohon') && $project->user_id !== $actor->id) {
            throw new WorkflowException('Anda tidak berhak memodifikasi permohonan ini.');
        }

        DB::transaction(function () use ($project, $actor, $oldStatus, $newStatus, $remarks) {
            $lockedProject = Project::where('id', $project->id)->lockForUpdate()->firstOrFail();

            if ($lockedProject->status !== $oldStatus) {
                throw new WorkflowException('Status project telah berubah di session lain. Muat ulang halaman.');
            }

            $lockedProject->update([
                'status' => $newStatus,
            ]);

            ApplicationLog::create([
                'project_id' => $project->id,
                'actor_id' => $actor->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks' => $remarks,
                'created_at' => now(),
            ]);
        });

        event(new ProjectStatusChanged($project->refresh(), $actor, $oldStatus, $newStatus, $remarks));

        return $project;
    }
}
