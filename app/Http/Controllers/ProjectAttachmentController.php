<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Models\ProjectPreview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectAttachmentController extends Controller
{
    /**
     * Serve a file inline (browser preview, not download).
     * Content-Disposition: inline → prevents IDM interception.
     */
    public function view(Request $request, Project $project, ProjectAttachment $attachment)
    {
        abort_if($attachment->project_id !== $project->id, 404);

        $this->authorize('view', $project);

        $filePath = storage_path('app/public/' . $attachment->file_url);

        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $realMime = $attachment->mime_type ?? mime_content_type($filePath);

        // ?stream=1 → serve as octet-stream to bypass IDM; frontend sets blob type manually
        $servedMime = $request->has('stream') ? 'application/octet-stream' : $realMime;

        return response()->file($filePath, [
            'Content-Type'              => $servedMime,
            'Content-Disposition'       => 'inline; filename="' . $attachment->file_name . '"',
            'X-Real-Mime'               => $realMime,
            'X-Content-Type-Options'    => 'nosniff',
            'Access-Control-Expose-Headers' => 'X-Real-Mime',
        ]);
    }

    public function store(Request $request, Project $project, ProjectPreview $preview)
    {
        abort_if($preview->project_id !== $project->id, 404);

        abort_if(
            !in_array($project->status, ['preview_sent', 'feedback_received']),
            403,
            'Attachments can only be uploaded while a preview is under review.'
        );

        $request->validate([
            'files'   => ['required', 'array', 'max:10'],
            'files.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,pdf,docs'],
        ]);

        foreach ($request->file('files') as $file) {
            $mime     = $file->getMimeType();
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs("projects/{$project->id}/attachments", $filename, 'public');

            ProjectAttachment::create([
                'project_id'  => $project->id,
                'preview_id'  => $preview->id,
                'file_name'   => $file->getClientOriginalName(),
                'file_url'    => $path,
                'file_type'   => str_starts_with($mime, 'image/') ? 'preview' : 'other',
                'file_size'   => $file->getSize(),
                'mime_type'   => $mime,
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now(),
            ]);
        }

        return back()->with('success', 'Files uploaded successfully.');
    }

    public function destroy(Project $project, ProjectAttachment $attachment)
    {
        abort_if($attachment->project_id !== $project->id, 404);

        if (Storage::disk('public')->exists($attachment->file_url)) {
            Storage::disk('public')->delete($attachment->file_url);
        }

        $attachment->deleted_by = Auth::id();
        $attachment->save();
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }
}
