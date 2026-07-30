<?php

namespace App\Http\Controllers\Api\Exports;

use App\Http\Controllers\Controller;
use App\Jobs\ExportProjectsToExcelJob;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectExportController extends Controller
{
    public function excel(Request $request): JsonResponse
    {
        $this->authorizeAnyRole($request);

        $filters = $request->only(['search', 'status', 'date_from', 'date_to']);
        $filename = 'exports/projects_' . now()->format('Ymd_His') . '.xlsx';

        ExportProjectsToExcelJob::dispatch($filters, $filename);

        return response()->json([
            'message' => 'Export Excel sedang diproses.',
            'file' => $filename,
            'download_url' => route('api.projects.export.download', ['path' => $filename]),
        ]);
    }

    public function download(Request $request)
    {
        $this->authorizeAnyRole($request);

        $path = $request->query('path');
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function pdf(Request $request, Project $project)
    {
        $this->authorizeAnyRole($request);

        $project->load(['user:id,name,email', 'documents', 'logs.actor:id,name']);

        $pdf = Pdf::loadView('exports.project-pdf', [
            'project' => $project,
        ])->setPaper('a4', 'portrait');

        return $pdf->download(sprintf('project-%s.pdf', $project->project_code));
    }

    public function certificate(Request $request, Project $project)
    {
        $this->authorizeAnyRole($request);

        abort_unless($project->certificate_path, 404);

        return Storage::disk('local')->download($project->certificate_path, sprintf('certificate-%s.pdf', $project->project_code));
    }

    private function authorizeAnyRole(Request $request): void
    {
        $user = $request->user();

        abort_unless($user && ($user->hasRole('pemohon') || $user->hasRole('penilai')), 403);
    }
}
