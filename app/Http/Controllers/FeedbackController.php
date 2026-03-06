<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function __construct(private ProjectService $projectService) {}

    public function store(Request $request, Project $project)
    {
        $this->authorize('submitFeedback', $project);

        // Only allowed when project is in an active review status
        abort_if(
            !in_array($project->status, ['preview_sent', 'feedback_received']),
            403,
            'Feedback can only be submitted when a preview is under review.'
        );

        $validated = $request->validate([
            'comment'       => ['required', 'string', 'max:5000'],
            'preview_id'    => ['nullable', 'uuid', 'exists:project_previews,id'],
            'attachments'   => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,pdf,doc,docx'],
        ]);

        $feedback = Feedback::create([
            'project_id'   => $project->id,
            'preview_id'   => $validated['preview_id'] ?? null,
            'comment'      => $validated['comment'],
            'submitted_by' => Auth::id(),
            'submitted_at' => now(),
            'is_active'    => true,
        ]);

        // Upload attachments if any
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $mime     = $file->getMimeType();
                $filename = time() . '_' . $file->getClientOriginalName();
                $path     = $file->storeAs("projects/{$project->id}/feedback-attachments", $filename, 'public');

                ProjectAttachment::create([
                    'project_id'  => $project->id,
                    'preview_id'  => $validated['preview_id'] ?? null,
                    'feedback_id' => $feedback->id,
                    'file_name'   => $file->getClientOriginalName(),
                    'file_url'    => $path,
                    'file_type'   => str_starts_with($mime, 'image/') ? 'preview' : 'other',
                    'file_size'   => $file->getSize(),
                    'mime_type'   => $mime,
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now(),
                ]);
            }
        }

        // Auto-transition project status preview_sent → feedback_received
        if ($project->status === 'preview_sent') {
            $oldStatus = $project->status;
            $project->update(['status' => 'feedback_received', 'updated_by' => Auth::id()]);
            $this->projectService->logStatusChange(
                $project, $oldStatus, 'feedback_received',
                'Feedback submitted by ' . Auth::user()->full_name
            );
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Feedback submitted successfully.');
    }

    public function destroy(Project $project, Feedback $feedback)
    {
        $this->authorize('deleteFeedback', [$project, $feedback]);

        $feedback->deleted_by = Auth::id();
        $feedback->save();
        $feedback->delete();

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Feedback deleted.');
    }
}
