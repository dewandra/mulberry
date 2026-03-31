<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Feedback;
use App\Models\Project;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{

    public function __construct(private CacheService $cache) {}

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $this->adminDashboard($user);
        }

        if ($user->isClient()) {
            return $this->clientDashboard($user);
        }

        if ($user->isPic()) {
            return $this->picDashboard($user);
        }

        return Inertia::render('Dashboard/Index', ['role' => $user->role]);
    }

    private function adminDashboard(User $user)
    {
        $cached = $this->cache->getAdminDashboardStats();
        $recentProjects = $this->cache->getAdminRecentProjects();

        return Inertia::render('Dashboard/Index', [
            'role'           => 'admin',
            'isSuperAdmin'   => $user->isSuperAdmin(),
            'stats'          => $cached['stats'],
            'byStatus'       => $cached['by_status'],
            'statuses'       => $cached['statuses'],
            'recentProjects' => $recentProjects,
        ]);
    }

    private function clientDashboard(User $user)
    {
        $projects = Project::with(['picUsers:id,full_name'])
            ->where('client_id', $user->client_id)
            ->latest('updated_at')
            ->get();

        $byStatus = $projects->groupBy('status')->map->count();
        $totalFeedback = Feedback::where('submitted_by', $user->id)->count();

        return Inertia::render('Dashboard/Index', [
            'role'          => 'client',
            'projects'      => $projects,
            'byStatus'      => $byStatus,
            'totalFeedback' => $totalFeedback,
        ]);
    }

    private function picDashboard(User $user)
    {
        $projects = Project::with(['client:id,company_name'])
            ->whereHas('pics', fn($q) => $q->where('pic_user_id', $user->id))
            ->latest('updated_at')
            ->get();

        $byStatus = $projects->groupBy('status')->map->count();
        $awaitingFeedback = $projects->where('status', 'preview_sent')->count();

        return Inertia::render('Dashboard/Index', [
            'role'            => 'pic',
            'projects'        => $projects,
            'byStatus'        => $byStatus,
            'awaitingFeedback'=> $awaitingFeedback,
        ]);
    }
}