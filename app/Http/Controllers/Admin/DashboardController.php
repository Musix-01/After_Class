<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Memory;
use App\Models\Mystery;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $stats = [
            'users'           => User::count(),
            'new_users_week'  => User::where('created_at', '>=', now()->subDays(7))->count(),
            'suspended'       => User::suspended()->count(),
            'posts'           => Post::count(),
            'posts_today'     => Post::where('created_at', '>=', today())->count(),
            'open_reports'    => Report::where('status', 'open')->count(),
            'locked_memories' => Memory::whereNull('opened_at')->where('unlock_at', '>', now())->count(),
            'open_mysteries'  => Mystery::where('status', 'open')->count(),
        ];

        return view('admin.dashboard', [
            'stats'  => $stats,
            'unread' => ActivityLog::unreadFor($request->user())->count(),
            'recent' => ActivityLog::with('user:id,name')->latest()->latest('id')->limit(8)->get(),
        ]);
    }
}
