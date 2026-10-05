<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:120'],
            'body'       => ['required', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ], [
            'expires_at.after' => 'The end date must be in the future.',
        ]);

        $announcement = Announcement::create($data + ['is_active' => true, 'created_by' => $request->user()->id]);

        ActivityLog::record('announcement.created', "Published announcement \"{$announcement->title}\"", $announcement);

        return back()->with('status', 'Announcement published. Students see it at the top of the wall.');
    }

    public function toggle(Announcement $announcement): RedirectResponse
    {
        $announcement->forceFill(['is_active' => ! $announcement->is_active])->save();

        ActivityLog::record('announcement.toggled', ($announcement->is_active ? 'Re-published' : 'Hid') . " announcement \"{$announcement->title}\"", $announcement);

        return back()->with('status', $announcement->is_active ? 'Announcement is live.' : 'Announcement hidden.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $title = $announcement->title;
        $announcement->delete();

        ActivityLog::record('announcement.deleted', "Deleted announcement \"{$title}\"");

        return back()->with('status', 'Announcement deleted.');
    }
}
