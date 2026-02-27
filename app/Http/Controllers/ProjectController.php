<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectPic;
use App\Models\ProjectStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function index(Request $request)
    {
        $user = Auth::user();

        $projects = Project::query()
            ->with(['client:id,company_name,logo_url', 'picUsers:id,full_name,email'])
            ->forUser($user)
            ->when($request->search, function ($query, $search) {
                $query->where('project_name', 'like', "%{$search}%")
                      ->orWhere('project_code', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->priority, function ($query, $priority) {
                $query->where('priority', $priority);
            })
            ->when($request->from, function ($query, $from) {
                $query->whereDate('deadline', '>=', $from);
            })
            ->when($request->to, function ($query, $to) {
                $query->whereDate('deadline', '<=', $to);
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $clients = Client::active()->get(['id', 'company_name']);

        // PIC users (for assignment in modal) — only for admin/super_admin
        $picUsers = [];
        if ($user->hasRole(['super_admin', 'admin'])) {
            $picUsers = User::where('role', 'pic')->active()->get(['id', 'full_name', 'email', 'client_id']);
        }

        return Inertia::render('Projects/Index', [
            'projects'  => $projects,
            'clients'   => $clients,
            'picUsers'  => $picUsers,
            'filters'   => $request->only(['search', 'status', 'priority', 'from', 'to']),
            'statuses'  => self::STATUSES,
            'canManage' => $user->hasRole(['super_admin', 'admin']),
        ]);
    }

    public function show(Project $project)
    {
        $user = Auth::user();

        // Gate: role-based access check
        if ($user->hasRole('client') && $project->client_id !== $user->client_id) {
            abort(403);
        }
        if ($user->hasRole('pic')) {
            $assigned = $project->pics()->where('pic_user_id', $user->id)->exists();
            if (!$assigned) abort(403);
        }

        $project->load([
            'client:id,company_name,logo_url',
            'picUsers:id,full_name,email',
            'previews' => function ($q) {
                $q->orderByDesc('sent_at')
                  ->with([
                      'sentBy:id,full_name',
                      'attachments' => function ($q) {
                          $q->orderBy('uploaded_at');
                      },
                      'feedbacks' => function ($q) {
                          $q->orderBy('submitted_at')
                            ->with('submittedBy:id,full_name,role');
                      },
                  ]);
            },
            'statusHistory' => function ($q) {
                $q->orderBy('changed_at')->with('changedBy:id,full_name');
            },
        ]);

        return Inertia::render('Projects/Show', [
            'project'     => $project,
            'statuses'    => self::STATUSES,
            'canManage'   => $user->hasRole(['super_admin', 'admin']),
            'canFeedback' => $user->hasRole(['client', 'pic', 'admin', 'super_admin']),
        ]);
    }

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
        $clientId = $validated['client_id'];
        $picIds   = $validated['pic_ids'] ?? [];
        unset($validated['pic_ids']);

        // Ensure every assigned PIC belongs to this client
        if (!empty($picIds)) {
            $invalidPics = User::whereIn('id', $picIds)
                ->where(function ($q) use ($clientId) {
                    $q->where('role', '!=', 'pic')
                      ->orWhere('client_id', '!=', $clientId);
                })->exists();
            if ($invalidPics) {
                return back()->withErrors(['pic_ids' => 'PIC harus berasal dari perusahaan client yang sama.']);
            }
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $file     = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('projects/thumbnails', $filename, 'public');
            $validated['thumbnail_url']      = $path;
            $validated['thumbnail_filename'] = $filename;
        }
        unset($validated['thumbnail']);

        $project = Project::create($validated);

        // Assign PICs
        if (!empty($picIds)) {
            foreach ($picIds as $picId) {
                ProjectPic::create([
                    'project_id'  => $project->id,
                    'pic_user_id' => $picId,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now(),
                ]);
            }
        }

        return redirect()->route('projects.index')
            ->with('success', "Project {$project->project_code} created successfully.");
    }

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
        $clientId = $validated['client_id'];

        $picIds = $validated['pic_ids'] ?? [];
        unset($validated['pic_ids']);

        // Ensure every assigned PIC belongs to this client
        if (!empty($picIds)) {
            $invalidPics = User::whereIn('id', $picIds)
                ->where(function ($q) use ($clientId) {
                    $q->where('role', '!=', 'pic')
                      ->orWhere('client_id', '!=', $clientId);
                })->exists();
            if ($invalidPics) {
                return back()->withErrors(['pic_ids' => 'PIC harus berasal dari perusahaan client yang sama.']);
            }
        }

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($project->thumbnail_url && Storage::disk('public')->exists($project->thumbnail_url)) {
                Storage::disk('public')->delete($project->thumbnail_url);
            }
            $file     = $request->file('thumbnail');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('projects/thumbnails', $filename, 'public');
            $validated['thumbnail_url']      = $path;
            $validated['thumbnail_filename'] = $filename;
        }
        unset($validated['thumbnail']);

        $project->update($validated);

        // Log status change if changed
        if ($oldStatus !== $project->status) {
            ProjectStatusHistory::create([
                'project_id'  => $project->id,
                'from_status' => $oldStatus,
                'to_status'   => $project->status,
                'notes'       => null,
                'changed_by'  => Auth::id(),
                'changed_at'  => now(),
            ]);
        }

        // Sync PICs
        ProjectPic::where('project_id', $project->id)->delete();
        foreach ($picIds as $picId) {
            ProjectPic::create([
                'project_id'  => $project->id,
                'pic_user_id' => $picId,
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ]);
        }

        return redirect()->route('projects.index')
            ->with('success', "Project {$project->project_code} updated successfully.");
    }

    public function destroy(Project $project)
    {
        $project->deleted_by = Auth::id();
        $project->save();
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', "Project {$project->project_code} deleted successfully.");
    }
}
