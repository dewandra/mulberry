<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Project;
use App\Models\ProjectStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'comment'    => ['required', 'string', 'max:5000'],
            'preview_id' => ['nullable', 'uuid', 'exists:project_previews,id'],
        ]);

        Feedback::create([
            'project_id'   => $project->id,
            'preview_id'   => $validated['preview_id'] ?? null,
            'comment'      => $validated['comment'],
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
            'is_active'    => true,
        ]);

        // Update project status → feedback_received (if currently preview_sent)
        if ($project->status === 'preview_sent') {
            $oldStatus = $project->status;
            $project->update([
                'status'     => 'feedback_received',
                'updated_by' => Auth::id(),
            ]);

            ProjectStatusHistory::create([
                'project_id'  => $project->id,
                'from_status' => $oldStatus,
                'to_status'   => 'feedback_received',
                'notes'       => 'Feedback submitted by ' . Auth::user()->full_name,
                'changed_by'  => Auth::id(),
                'changed_at'  => now(),
            ]);
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Feedback submitted successfully.');
    }

    public function destroy(Project $project, Feedback $feedback)
    {
        abort_if($feedback->project_id !== $project->id, 404);

        // Only the submitter or admin can delete
        $user = Auth::user();
        if ($feedback->submitted_by !== $user->id && !$user->hasRole(['super_admin', 'admin'])) {
            abort(403);
        }

        $feedback->deleted_by = Auth::id();
        $feedback->save();
        $feedback->delete();

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Feedback deleted.');
    }
}
