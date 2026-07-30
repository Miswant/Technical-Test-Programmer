<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectIndexRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\TransitionProjectRequest;
use App\Models\Project;
use App\Enums\ProjectStatus;
use App\Services\ProjectWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectWorkflowService $workflow
    ) {}

    public function index(ProjectIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $perPage = $validated['per_page'] ?? 15;

        $query = Project::query()
            ->with(['user:id,name,email', 'documents:id,project_id,file_name,mime_type'])
            ->when($user->hasRole('pemohon'), fn ($q) => $q->where('user_id', $user->id))
            ->when(
                $validated['search'] ?? null,
                fn ($q, $search) => $q->where(function ($q) use ($search) {
                    $q->where('title', 'ilike', "%{$search}%")
                      ->orWhere('project_code', 'ilike', "%{$search}%");
                })
            )
            ->when(
                $validated['status'] ?? null,
                fn ($q, $status) => $q->where('status', $status)
            )
            ->when(
                $validated['date_from'] ?? null,
                fn ($q, $date) => $q->whereDate('created_at', '>=', $date)
            )
            ->when(
                $validated['date_to'] ?? null,
                fn ($q, $date) => $q->whereDate('created_at', '<=', $date)
            )
            ->orderByDesc('created_at');

        $projects = $query->cursorPaginate($perPage);

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $project = Project::create([
            'project_code' => 'PRJ-' . strtoupper(Str::random(8)),
            'user_id'      => $user->id,
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'status'       => ProjectStatus::DRAFT,
        ]);

        $this->workflow->transition($project, $user, ProjectStatus::DRAFT, 'Permohonan baru dibuat.');

        $project->load(['user:id,name,email', 'documents']);

        return response()->json(['data' => $project], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $user = request()->user();

        if ($user->hasRole('pemohon') && $project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $project->load([
            'user:id,name,email',
            'documents',
            'logs' => fn ($q) => $q->with('actor:id,name'),
        ]);

        return response()->json(['data' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if ($project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!in_array($project->status, [ProjectStatus::DRAFT, ProjectStatus::REVISION_REQUIRED])) {
            abort(422, 'Project hanya dapat diedit pada status DRAFT atau REVISION_REQUIRED.');
        }

        $project->update($request->validated());
        $project->load(['user:id,name,email', 'documents']);

        return response()->json(['data' => $project]);
    }

    public function transition(TransitionProjectRequest $request, Project $project): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $newStatus = ProjectStatus::from($validated['status']);

        // Otorisasi: Pemohon hanya boleh SUBMITTED / REVISED
        if ($user->hasRole('pemohon')) {
            if ($project->user_id !== $user->id) {
                abort(403, 'Akses ditolak.');
            }
            if (!in_array($newStatus, [ProjectStatus::SUBMITTED, ProjectStatus::REVISED])) {
                abort(403, 'Pemohon hanya dapat submit/submit ulang permohonan.');
            }
        }

        // Otorisasi: Penilai hanya boleh APPROVED / REVISION_REQUIRED / REJECTED
        if ($user->hasRole('penilai')) {
            if (!in_array($newStatus, [ProjectStatus::APPROVED, ProjectStatus::REVISION_REQUIRED, ProjectStatus::REJECTED])) {
                abort(403, 'Penilai hanya dapat approve, request revision, atau reject.');
            }
        }

        $this->workflow->transition($project, $user, $newStatus, $validated['remarks'] ?? null);

        $project->load(['user:id,name,email', 'documents', 'logs.actor:id,name']);

        return response()->json(['data' => $project]);
    }

    public function dashboard(): JsonResponse
    {
        $user = request()->user();

        $query = Project::query()
            ->when($user->hasRole('pemohon'), fn ($q) => $q->where('user_id', $user->id));

        $stats = $query->selectRaw("
            COUNT(*) FILTER (WHERE status = 'DRAFT')              AS draft,
            COUNT(*) FILTER (WHERE status = 'SUBMITTED')          AS submitted,
            COUNT(*) FILTER (WHERE status = 'REVISION_REQUIRED')  AS revision_required,
            COUNT(*) FILTER (WHERE status = 'REVISED')            AS revised,
            COUNT(*) FILTER (WHERE status = 'APPROVED')           AS approved,
            COUNT(*) FILTER (WHERE status = 'REJECTED')           AS rejected,
            COUNT(*)                                              AS total
        ")->first();

        return response()->json(['data' => $stats]);
    }

    public function logs(Project $project): JsonResponse
    {
        $user = request()->user();

        if ($user->hasRole('pemohon') && $project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $logs = $project->logs()
            ->with('actor:id,name')
            ->orderByDesc('created_at')
            ->cursorPaginate(30);

        return response()->json($logs);
    }
}
