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
    public function store(Request $request, Project $project, ProjectPreview $preview)
    {
        abort_if($preview->project_id !== $project->id, 404);

        $request->validate([
            'files'   => ['required', 'array', 'max:10'],
            'files.*' => ['file', 'max:20480'], // 20MB per file
        ]);

        foreach ($request->file('files') as $file) {
            $mime     = $file->getMimeType();
            $filename = time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs("projects/{$project->id}/attachments", $filename, 'public');

            // Determine file_type from mime
            $fileType = str_starts_with($mime, 'image/') ? 'preview' : 'other';

            ProjectAttachment::create([
                'project_id'  => $project->id,
                'preview_id'  => $preview->id,
                'file_name'   => $file->getClientOriginalName(),
                'file_url'    => $path,
                'file_type'   => $fileType,
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
