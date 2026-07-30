<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectIndexRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\TransitionProjectRequest;
use App\Models\Project;
use App\Models\ApplicationLog;
use App\Enums\ProjectStatus;
use App\Services\ProjectWorkflowService;
use App\Exceptions\WorkflowException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

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
            ->when($validated['search'] ?? null, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('project_code', 'ilike', "%{$search}%");
            }))
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['date_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at');

        return response()->json($query->cursorPaginate($perPage));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $project = DB::transaction(function () use ($validated, $user) {
            $proj = Project::create([
                'project_code' => 'PRJ-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'status' => ProjectStatus::DRAFT,
            ]);

            ApplicationLog::create([
                'project_id' => $proj->id,
                'actor_id' => $user->id,
                'old_status' => null,
                'new_status' => ProjectStatus::DRAFT,
                'remarks' => 'Permohonan baru diinisiasi oleh Pemohon.',
                'created_at' => now(),
            ]);

            return $proj;
        });

        $project->load(['user:id,name,email', 'documents']);

        return response()->json(['data' => $project], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $user = request()->user();

        if ($user->hasRole('pemohon') && $project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $project->load(['user:id,name,email', 'documents', 'logs' => fn ($q) => $q->with('actor:id,name')]);

        return response()->json([
            'data' => $project,
            'certificate_url' => $project->certificate_path ? route('api.projects.certificate.download', ['project' => $project->id]) : null,
        ]);
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

        try {
            $project = $this->workflow->transition($project, $user, $newStatus, $validated['remarks'] ?? null);
        } catch (WorkflowException $e) {
            abort(422, $e->getMessage());
        }

        $project->load(['user:id,name,email', 'documents', 'logs.actor:id,name']);

        return response()->json([
            'data' => $project,
            'certificate_url' => $project->certificate_path ? route('api.projects.certificate.download', ['project' => $project->id]) : null,
        ]);
    }

    public function dashboard(): JsonResponse
    {
        $user = request()->user();
        $cacheKey = $this->workflow->dashboardCacheKey($user->hasRole('pemohon') ? $user->id : null);

        $stats = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            $query = Project::query()->when($user->hasRole('pemohon'), fn ($q) => $q->where('user_id', $user->id));

            return $query->selectRaw("COUNT(*) FILTER (WHERE status = 'DRAFT') AS draft, COUNT(*) FILTER (WHERE status = 'SUBMITTED') AS submitted, COUNT(*) FILTER (WHERE status = 'REVISION_REQUIRED') AS revision_required, COUNT(*) FILTER (WHERE status = 'REVISED') AS revised, COUNT(*) FILTER (WHERE status = 'APPROVED') AS approved, COUNT(*) FILTER (WHERE status = 'REJECTED') AS rejected, COUNT(*) AS total")->first();
        });

        return response()->json(['data' => $stats]);
    }

    public function chart(): JsonResponse
    {
        $user = request()->user();
        $cacheKey = $this->workflow->dashboardCacheKey($user->hasRole('pemohon') ? $user->id : null) . '.chart';

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            $baseQuery = Project::query()->when($user->hasRole('pemohon'), fn ($q) => $q->where('user_id', $user->id));

            $daily = (clone $baseQuery)
                ->selectRaw("DATE(created_at) as date, COUNT(*) as total")
                ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                ->groupByRaw('DATE(created_at)')
                ->orderBy('date')
                ->get();

            $monthly = (clone $baseQuery)
                ->selectRaw("DATE_TRUNC('month', created_at) as month, COUNT(*) as total")
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupByRaw("DATE_TRUNC('month', created_at)")
                ->orderBy('month')
                ->get();

            return [
                'daily' => $daily,
                'monthly' => $monthly,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function logs(Project $project): JsonResponse
    {
        $user = request()->user();

        if ($user->hasRole('pemohon') && $project->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $logs = $project->logs()->with('actor:id,name')->orderByDesc('created_at')->cursorPaginate(30);

        return response()->json($logs);
    }
}
