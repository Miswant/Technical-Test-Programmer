<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectDocumentRequest;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Enums\ProjectStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProjectDocumentController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        $user = request()->user();

        if ($user->hasRole('pemohon') && $project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $documents = $project->documents()->get();

        return response()->json(['data' => $documents]);
    }

    public function store(StoreProjectDocumentRequest $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if ($project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($project->status, [ProjectStatus::DRAFT, ProjectStatus::REVISION_REQUIRED])) {
            abort(422, 'Dokumen hanya dapat diunggah pada status DRAFT atau REVISION_REQUIRED.');
        }

        $file = $request->file('file');
        $path = $file->store("projects/{$project->id}/documents", 'public');

        $document = $project->documents()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json(['data' => $document], 201);
    }

    public function destroy(Project $project, ProjectDocument $document): JsonResponse
    {
        $user = request()->user();

        if ($project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($project->status, [ProjectStatus::DRAFT, ProjectStatus::REVISION_REQUIRED])) {
            abort(422, 'Dokumen hanya dapat dihapus pada status DRAFT atau REVISION_REQUIRED.');
        }

        if ($document->project_id !== $project->id) {
            abort(404);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(null, 204);
    }
}
