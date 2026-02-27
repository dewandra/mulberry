<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPreview;
use App\Models\ProjectStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectPreviewController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title'           => ['nullable', 'string', 'max:255'],
            'description'     => ['required', 'string'],
            'internal_notes'  => ['nullable', 'string'],
            'review_deadline' => ['nullable', 'date'],
        ]);

        // Auto-increment version
        $lastVersion = $project->previews()->max('version') ?? 'v0';
        $nextNum     = (int) ltrim($lastVersion, 'v') + 1;
        $version     = 'v' . $nextNum;

        $preview = $project->previews()->create([
            'version'         => $version,
            'title'           => $validated['title'] ?? "Preview {$version}",
            'description'     => $validated['description'],
            'internal_notes'  => $validated['internal_notes'] ?? null,
            'review_deadline' => $validated['review_deadline'] ?? null,
            'sent_at'         => now(),
            'sent_by'         => Auth::id(),
            'is_active'       => true,
        ]);

        // Update project status → preview_sent (and log)
        $oldStatus = $project->status;
        $project->update([
            'status'             => 'preview_sent',
            'current_preview_id' => $preview->id,
            'updated_by'         => Auth::id(),
        ]);

        if ($oldStatus !== 'preview_sent') {
            ProjectStatusHistory::create([
                'project_id'  => $project->id,
                'from_status' => $oldStatus,
                'to_status'   => 'preview_sent',
                'notes'       => "Preview {$version} sent",
                'changed_by'  => Auth::id(),
                'changed_at'  => now(),
            ]);
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', "Preview {$version} sent successfully.");
    }

    public function destroy(Project $project, ProjectPreview $preview)
    {
        abort_if($preview->project_id !== $project->id, 404);

        $preview->deleted_by = Auth::id();
        $preview->save();
        $preview->delete();

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Preview deleted.');
    }
}
