<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPreview extends Model
{
    use HasUuids, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'version',
        'title',
        'description',
        'internal_notes',
        'review_deadline',
        'sent_at',
        'sent_by',
        'is_active',
        'deleted_by',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'sent_at'         => 'datetime',
        'review_deadline' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function sentBy()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class, 'preview_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'preview_id');
    }
}
