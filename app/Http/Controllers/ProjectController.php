<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProjectController extends Controller
{
    private const STATUSES = [
        'brief',
        'scheduled',
        'work_in_progress',
        'preview_sent',
        'feedback_received',
        'artwork_approved',
        'final_artwork_preparation',
        'fa_sent',
        'project_closed',
    ];

    public function __construct(private ProjectService $projectService) {}

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $projects = Project::query()
            ->with(['client:id,company_name,logo_url', 'picUsers:id,full_name,email'])
            ->forUser($user)
            ->when($request->search, fn ($q, $s) => $q->where('project_name', 'like', "%{$s}%")
                                                       ->orWhere('project_code', 'like', "%{$s}%"))
            ->when($request->status,   fn ($q, $v) => $q->where('status', $v))
            ->when($request->priority, fn ($q, $v) => $q->where('priority', $v))
            ->when($request->from,     fn ($q, $v) => $q->whereDate('deadline', '>=', $v))
            ->when($request->to,       fn ($q, $v) => $q->whereDate('deadline', '<=', $v))
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        $clients  = Client::active()->get(['id', 'company_name']);
        $picUsers = $user->hasRole(['super_admin', 'admin'])
            ? User::where('role', 'pic')->active()->get(['id', 'full_name', 'email', 'client_id'])
            : [];

        return Inertia::render('Projects/Index', [
            'projects'  => $projects,
            'clients'   => $clients,
            'picUsers'  => $picUsers,
            'filters'   => $request->only(['search', 'status', 'priority', 'from', 'to']),
            'statuses'  => self::STATUSES,
            'canManage' => $user->hasRole(['super_admin', 'admin']),
        ]);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Project $project)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->authorize('view', $project);

        $project->load([
            'client:id,company_name,logo_url',
            'picUsers:id,full_name,email',
            'previews' => fn ($q) => $q->orderByDesc('sent_at')->with([
                'sentBy:id,full_name',
                'attachments' => fn ($q) => $q->orderBy('uploaded_at'),
                'feedbacks'   => fn ($q) => $q->orderBy('submitted_at')->with([
                    'submittedBy:id,full_name,role',
                    'attachments',
                ]),
            ]),
            'statusHistory' => fn ($q) => $q->orderBy('changed_at')->with('changedBy:id,full_name'),
        ]);

        return Inertia::render('Projects/Show', [
            'project'     => $project,
            'statuses'    => self::STATUSES,
            'canManage'   => $user->hasRole(['super_admin', 'admin']),
            'canFeedback' => $user->hasRole(['client', 'pic']),
        ]);
    }

    // ─── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'client_id'    => ['required', 'uuid', 'exists:clients,id'],
            'status'       => ['required', Rule::in(self::STATUSES)],
            'priority'     => ['required', Rule::in(['high', 'normal', 'low'])],
            'description'  => ['nullable', 'string'],
            'deadline'     => ['nullable', 'date'],
            'report_date'  => ['nullable', 'date'],
            'pic_ids'      => ['nullable', 'array'],
            'pic_ids.*'    => ['uuid', 'exists:users,id'],
            'thumbnail'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $validated['created_by'] = Auth::id();
        $picIds   = $validated['pic_ids'] ?? [];
        $clientId = $validated['client_id'];
        unset($validated['pic_ids']);

        if (!$this->projectService->validatePics($picIds, $clientId)) {
            return back()->withErrors(['pic_ids' => 'PIC harus berasal dari perusahaan client yang sama.']);
        }

        if ($request->hasFile('thumbnail')) {
            $validated += $this->projectService->handleThumbnailUpload($request->file('thumbnail'));
        }
        unset($validated['thumbnail']);

        $project = Project::create($validated);
        $this->projectService->assignPics($project, $picIds);

        return redirect()->route('projects.index')
            ->with('success', "Project {$project->project_code} created successfully.");
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'client_id'    => ['required', 'uuid', 'exists:clients,id'],
            'status'       => ['required', Rule::in(self::STATUSES)],
            'priority'     => ['required', Rule::in(['high', 'normal', 'low'])],
            'description'  => ['nullable', 'string'],
            'deadline'     => ['nullable', 'date'],
            'report_date'  => ['nullable', 'date'],
            'pic_ids'      => ['nullable', 'array'],
            'pic_ids.*'    => ['uuid', 'exists:users,id'],
            'thumbnail'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $oldStatus = $project->status;
        $validated['updated_by'] = Auth::id();
        $picIds   = $validated['pic_ids'] ?? [];
        $clientId = $validated['client_id'];
        unset($validated['pic_ids']);

        if (!$this->projectService->validatePics($picIds, $clientId)) {
            return back()->withErrors(['pic_ids' => 'PIC harus berasal dari perusahaan client yang sama.']);
        }

        if ($request->hasFile('thumbnail')) {
            $validated += $this->projectService->handleThumbnailUpload(
                $request->file('thumbnail'),
                $project->thumbnail_url
            );
        }
        unset($validated['thumbnail']);

        $project->update($validated);

        if ($oldStatus !== $project->status) {
            $this->projectService->logStatusChange($project, $oldStatus, $project->status);
        }

        $this->projectService->syncPics($project, $picIds);

        return redirect()->route('projects.show', $project->id)
            ->with('success', "Project {$project->project_code} updated successfully.");
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────

    public function destroy(Project $project)
    {
        $project->deleted_by = Auth::id();
        $project->save();
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', "Project {$project->project_code} deleted successfully.");
    }
}
