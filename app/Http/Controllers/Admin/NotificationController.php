<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /** Things that need an admin's attention: new reports, new sign-ups, new theories. */
    public function index(Request $request): View
    {
        $seenAt = $request->user()->admin_seen_at;

        $notifications = ActivityLog::query()
            ->with('user:id,name')
            ->where('notify', true)
            ->latest()
            ->latest('id')
            ->simplePaginate(20);

        return view('admin.notifications.index', compact('notifications', 'seenAt'));
    }

    public function read(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['admin_seen_at' => now()])->save();

        return back()->with('status', 'All caught up.');
    }
}
