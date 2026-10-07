<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(10);

        return view('users.index', compact('notifications'));
    }

    public function read($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        $notification->update([
            'is_read' => true,
        ]);

        return back();
    }
}