<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'project_name',
        'project_code',
        'client_id',
        'status',
        'priority',
        'description',
        'deadline',
        'report_date',
        'artwork_approved_at',
        'current_preview_id',
        'thumbnail_url',
        'thumbnail_filename',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'deadline'            => 'date',
        'report_date'         => 'date',
        'artwork_approved_at' => 'datetime',
    ];

    protected $appends = [
        'status_display',
        'priority_display',
        'priority_badge_color',
        'status_badge_color',
        'thumbnail',
    ];

    // ─── Boot ────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Project $project) {
            if (empty($project->project_code)) {
                $project->project_code = static::generateProjectCode();
            }
        });

        static::created(function (Project $project) {
            // Log initial status
            $project->statusHistory()->create([
                'from_status' => null,
                'to_status'   => $project->status,
                'notes'       => 'Project created',
                'changed_by'  => $project->created_by,
                'changed_at'  => now(),
            ]);
        });
    }

    public static function generateProjectCode(): string
    {
        $year = now()->year;
        $prefix = "ACT-{$year}-";

        $last = static::withTrashed()
            ->where('project_code', 'like', "{$prefix}%")
            ->orderByDesc('project_code')
            ->lockForUpdate()
            ->value('project_code');

        $next = $last ? (int) substr($last, strlen($prefix)) + 1 : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pics()
    {
        return $this->hasMany(ProjectPic::class);
    }

    public function picUsers()
    {
        return $this->belongsToMany(User::class, 'project_pics', 'project_id', 'pic_user_id')
                    ->withPivot(['assigned_at', 'assigned_by']);
    }

    public function statusHistory()
    {
        return $this->hasMany(ProjectStatusHistory::class);
    }

    public function previews()
    {
        return $this->hasMany(ProjectPreview::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Apply role-based project visibility.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return $query; // all projects
        }

        if ($user->isClient()) {
            return $query->where('client_id', $user->client_id);
        }

        if ($user->isPic()) {
            return $query->whereHas('pics', function ($q) use ($user) {
                $q->where('pic_user_id', $user->id);
            });
        }

        // fallback: no projects
        return $query->whereRaw('0 = 1');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'brief'                    => 'Brief',
            'scheduled'                => 'Scheduled',
            'work_in_progress'         => 'Work In Progress',
            'preview_sent'             => 'Preview Sent',
            'feedback_received'        => 'Feedback Received',
            'artwork_approved'         => 'Artwork Approved',
            'final_artwork_preparation'=> 'Final Artwork Preparation',
            'fa_sent'                  => 'FA Sent',
            'project_closed'           => 'Project Closed',
            default                    => ucfirst($this->status),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'brief'                    => 'gray',
            'scheduled'                => 'blue',
            'work_in_progress'         => 'yellow',
            'preview_sent'             => 'purple',
            'feedback_received'        => 'orange',
            'artwork_approved'         => 'green',
            'final_artwork_preparation'=> 'teal',
            'fa_sent'                  => 'indigo',
            'project_closed'           => 'dark',
            default                    => 'gray',
        };
    }

    public function getPriorityDisplayAttribute(): string
    {
        return match($this->priority) {
            'high'   => 'High',
            'normal' => 'Normal',
            'low'    => 'Low',
            default  => ucfirst($this->priority),
        };
    }

    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'high'   => 'red',
            'normal' => 'blue',
            'low'    => 'gray',
            default  => 'blue',
        };
    }

    public function getThumbnailAttribute(): ?string
    {
        return $this->thumbnail_url ? asset('storage/' . $this->thumbnail_url) : null;
    }
}
