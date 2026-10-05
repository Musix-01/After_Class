<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    /** Filter groups: the part of the action before the dot. */
    public const GROUPS = [
        'auth'         => 'Logins',
        'user'         => 'Accounts',
        'post'         => 'Posts',
        'comment'      => 'Comments',
        'report'       => 'Reports',
        'memory'       => 'Memories',
        'mystery'      => 'Mysteries',
        'announcement' => 'Announcements',
    ];

    public function index(Request $request): View
    {
        $q     = trim((string) $request->query('q'));
        $group = $request->query('group');
        $date  = $request->query('date');

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->when($q !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('description', 'like', "%{$q}%")
                    ->orWhere('ip_address', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
            ))
            ->when(isset(self::GROUPS[$group]), fn ($query) => $query->where('action', 'like', $group . '.%'))
            ->when($date, fn ($query) => $query->whereDate('created_at', $date))
            ->latest()
            ->latest('id')
            ->simplePaginate(25)
            ->withQueryString();

        return view('admin.logs.index', ['logs' => $logs, 'groups' => self::GROUPS] + compact('q', 'group', 'date'));
    }
}
