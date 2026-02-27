<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feedback extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'feedbacks';

    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'preview_id',
        'comment',
        'submitted_by',
        'submitted_at',
        'is_active',
        'deleted_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function preview()
    {
        return $this->belongsTo(ProjectPreview::class, 'preview_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectAttachment::class, 'feedback_id');
    }
}
