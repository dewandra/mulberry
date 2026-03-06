<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectAttachment extends Model
{
    use HasUuids, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'preview_id',
        'feedback_id',
        'file_name',
        'file_url',
        'file_type',
        'file_size',
        'mime_type',
        'uploaded_by',
        'uploaded_at',
        'deleted_by',
    ];

    protected $appends = ['url'];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'file_size'   => 'integer',
    ];

    public function getUrlAttribute(): string
    {
        // Gunakan route inline view — file di-serve dengan Content-Disposition: inline
        // sehingga browser bisa preview (PDF, gambar) tanpa trigger download manager
        return route('projects.attachments.view', [
            'project'    => $this->project_id,
            'attachment' => $this->id,
        ]);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function preview()
    {
        return $this->belongsTo(ProjectPreview::class, 'preview_id');
    }

    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'feedback_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
