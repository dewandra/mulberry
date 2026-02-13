<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Different dashboard data based on role
        $data = [
            'user' => $user->load('client'),
        ];

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Admin dashboard data
            $data['stats'] = [
                'total_users' => \App\Models\User::count(),
                'total_clients' => \App\Models\Client::count(),
                'active_users' => \App\Models\User::active()->count(),
                'active_clients' => \App\Models\Client::active()->count(),
            ];
        }

        return Inertia::render('Dashboard/Index', $data);
    }
}