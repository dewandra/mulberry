<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    // ─── Cache Keys ──────────────────────────────────────────────────────────
    public const KEY_ADMIN_STATS           = 'dashboard:admin_stats';
    public const KEY_ADMIN_RECENT_PROJECTS = 'dashboard:admin_recent_projects';
    public const KEY_ACTIVE_CLIENTS        = 'dropdown:active_clients';
    public const KEY_PIC_USERS             = 'dropdown:pic_users';
    // ─── TTL (dalam detik) ───────────────────────────────────────────────────
    public const TTL_DASHBOARD = 300;  // 5 menit
    public const TTL_DROPDOWN  = 1800; // 30 menit
    // ─── Dashboard Admin ─────────────────────────────────────────────────────
    
    public function getAdminDashboardStats(): array 
    {
        return Cache::remember(self::KEY_ADMIN_STATS, self::TTL_DASHBOARD,  function () 
        {
            $statuses = [
                'brief', 'scheduled', 'work_in_progress', 'preview_sent',
                'feedback_received', 'artwork_approved', 'final_artwork_preparation',
                'fa_sent', 'project_closed',
            ];
            
            $statusCounts = Project::selectRaw('status, COUNT(*) AS count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
            
            $totalProjects = array_sum($statusCounts);
            
            $byStatus = [];
            foreach ($statuses as $s) {
                $byStatus[$s] = $statusCounts[$s] ?? 0;
            }
            
            return [
                'stats' => [
                    'total_projects' => $totalProjects,
                    'total_clients' => Client::count(),
                    'total_users' => User::count(),
                    'awaiting_feedback' => $byStatus['preview_sent'] ?? 0,
                    'feedback_received' => $byStatus['feedback_received'] ?? 0,
                    'high_priority' => Project::where('priority', 'high')->count(),
                    'active_projects' => $totalProjects - ($byStatus['project_closed'] ?? 0),
                    'closed_projects' => $byStatus['project_closed'] ?? 0,
                ],
                'by_status' => $byStatus,
                'statuses' => $statuses
            ];
        });
    }
    
    public function getAdminRecentProjects()
    {
        return Cache::remember(self::KEY_ADMIN_RECENT_PROJECTS, self::TTL_DASHBOARD, function () 
        {
            return Project::with([
                'client:id,company_name',
                'picUsers:id,full_name'
                ])
                ->orderBy('updated_at', 'desc')
                ->limit(6)
                ->get();
        });
    }
        
        
    // ─── Dropdown Lists ─────────────────────────────────────────────────────
    public function getActiveClients()
    {
        return Cache::remember(self::KEY_ACTIVE_CLIENTS, self::TTL_DROPDOWN, function() 
        {
            return Client::active()->get(['id', 'company_name']);
        });
    }

    public function getPicUsers()
    {
        return Cache::remember(self::KEY_PIC_USERS, self::TTL_DROPDOWN, function() 
        {
            return User::where('role', 'pic')->active()->get(['id', 'full_name', 'email', 'client_id']);
        });
    }

    public function invalidateProjectCaches(): void
    {
        Cache::forget(self::KEY_ADMIN_STATS);
        Cache::forget(self::KEY_ADMIN_RECENT_PROJECTS);
    }

    public function invalidateClientCaches(): void
    {
        Cache::forget(self::KEY_ACTIVE_CLIENTS);
        Cache::forget(self::KEY_ADMIN_STATS);
    }

    public function invalidateUserCaches(): void
    {
        Cache::forget(self::KEY_PIC_USERS);
        Cache::forget(self::KEY_ADMIN_STATS);
    }

    public function clearAll(): void
    {
        Cache::forget(self::KEY_ADMIN_STATS);
        Cache::forget(self::KEY_ADMIN_RECENT_PROJECTS);
        Cache::forget(self::KEY_ACTIVE_CLIENTS);
        Cache::forget(self::KEY_PIC_USERS);
    }
    
}




