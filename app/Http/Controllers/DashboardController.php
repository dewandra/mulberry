<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Feedback;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
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
        $statuses = [
            'brief', 'scheduled', 'work_in_progress', 'preview_sent',
            'feedback_received', 'artwork_approved', 'final_artwork_preparation',
            'fa_sent', 'project_closed',
        ];

        // Count projects per status
        $statusCounts = Project::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalProjects = array_sum($statusCounts);

        // Fill missing statuses with 0
        $byStatus = [];
        foreach ($statuses as $s) {
            $byStatus[$s] = $statusCounts[$s] ?? 0;
        }

        $stats = [
            'total_projects'    => $totalProjects,
            'total_clients'     => Client::count(),
            'total_users'       => User::count(),
            'awaiting_feedback' => $byStatus['preview_sent'] ?? 0,
            'feedback_received' => $byStatus['feedback_received'] ?? 0,
            'high_priority'     => Project::where('priority', 'high')->count(),
            'active_projects'   => $totalProjects - ($byStatus['project_closed'] ?? 0),
        ];

        // Recent projects (last 8)
        $recentProjects = Project::with(['client:id,company_name', 'picUsers:id,full_name'])
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return Inertia::render('Dashboard/Index', [
            'role'           => 'admin',
            'isSuperAdmin'   => $user->isSuperAdmin(),
            'stats'          => $stats,
            'byStatus'       => $byStatus,
            'statuses'       => $statuses,
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