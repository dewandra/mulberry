<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Models\ProjectPic;
use App\Models\ProjectStatusHistory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProjectService
{
    /**
     * Validate that every PIC in $picIds belongs to the given client
     * and has the 'pic' role. Returns true if all valid.
     */
    public function validatePics(array $picIds, string $clientId): bool
    {
        if (empty($picIds)) {
            return true;
        }

        return !User::whereIn('id', $picIds)
            ->where(function ($q) use ($clientId) {
                $q->where('role', '!=', 'pic')
                  ->orWhere('client_id', '!=', $clientId);
            })->exists();
    }

    /**
     * Store a thumbnail file to disk.
     * Deletes the $oldPath first if provided.
     * Returns the fields array to merge into validated data.
     */
    public function handleThumbnailUpload(UploadedFile $file, ?string $oldPath = null): array
    {
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $path     = $file->storeAs('projects/thumbnails', $filename, 'public');

        return [
            'thumbnail_url'      => $path,
            'thumbnail_filename' => $filename,
        ];
    }

    /**
     * Assign PICs to a newly created project (no prior records to delete).
     */
    public function assignPics(Project $project, array $picIds): void
    {
        foreach ($picIds as $picId) {
            ProjectPic::create([
                'project_id'  => $project->id,
                'pic_user_id' => $picId,
                'assigned_by' => Auth::id(),
                'assigned_at' => now(),
            ]);
        }
    }

    /**
     * Sync PICs for an existing project:
     * removes all existing assignments then inserts the new set.
     */
    public function syncPics(Project $project, array $picIds): void
    {
        ProjectPic::where('project_id', $project->id)->delete();
        $this->assignPics($project, $picIds);
    }

    /**
     * Write an entry to the status history log.
     */
    public function logStatusChange(
        Project $project,
        string  $fromStatus,
        string  $toStatus,
        ?string $notes = null
    ): void {
        ProjectStatusHistory::create([
            'project_id'  => $project->id,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'notes'       => $notes,
            'changed_by'  => Auth::id(),
            'changed_at'  => now(),
        ]);
    }
}
