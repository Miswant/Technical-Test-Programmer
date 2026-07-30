<?php

namespace App\Services\Certificates;

use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateGeneratorService
{
    public function generate(Project $project): Project
    {
        $project->loadMissing(['user', 'documents', 'logs.actor']);

        $pdf = Pdf::loadView('exports.certificate', [
            'project' => $project,
        ])->setPaper('a4', 'portrait');

        $path = sprintf('certificates/%s/%s.pdf', $project->id, Str::slug($project->project_code));

        Storage::disk('local')->put($path, $pdf->output());

        $project->forceFill([
            'certificate_path' => $path,
            'certificate_generated_at' => now(),
        ])->save();

        return $project->refresh();
    }

    public function downloadUrl(Project $project): ?string
    {
        if (!$project->certificate_path) {
            return null;
        }

        return route('api.projects.certificate.download', ['project' => $project->id]);
    }
}
