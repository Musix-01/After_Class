<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $q      = trim((string) $request->query('q'));
        $role   = $request->query('role');
        $status = $request->query('status');

        $users = User::query()
            ->withCount(['posts', 'comments'])
            ->when($q !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
            ))
            ->when(in_array($role, ['student', 'admin'], true), fn ($query) => $query->where('role', $role))
            ->when($status === 'suspended', fn ($query) => $query->suspended())
            ->when($status === 'active', fn ($query) => $query->whereNotIn('id', User::suspended()->select('id')))
            ->latest()
            ->simplePaginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q', 'role', 'status'));
    }

    public function show(User $user): View
    {
        $user->loadCount(['posts', 'comments']);

        return view('admin.users.show', [
            'user'         => $user,
            'posts'        => $user->posts()->withTrashed()->latest()->limit(8)->get(),
            'logs'         => ActivityLog::where('user_id', $user->id)->latest()->latest('id')->limit(10)->get(),
            'reportsAbout' => Report::whereIn('post_id', $user->posts()->withTrashed()->select('id'))->count(),
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin() || $user->is($request->user())) {
            return back()->withErrors(['user' => 'Admin accounts cannot be suspended. Remove the admin role first.']);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:200'],
            'until'  => ['nullable', 'date', 'after:now'],
        ], [
            'reason.required' => 'Give a reason so the student knows why.',
            'until.after'     => 'The end date must be in the future.',
        ]);

        $user->forceFill([
            'suspended_at'      => now(),
            'suspended_until'   => $data['until'] ?? null,
            'suspension_reason' => $data['reason'],
        ])->save();

        ActivityLog::record(
            'user.suspended',
            "Suspended {$user->name}: {$data['reason']}",
            $user,
            ['until' => $data['until'] ?? 'indefinitely']
        );

        return back()->with('status', "{$user->name} is suspended.");
    }

    public function unsuspend(User $user): RedirectResponse
    {
        $user->forceFill(['suspended_at' => null, 'suspended_until' => null, 'suspension_reason' => null])->save();

        ActivityLog::record('user.unsuspended', "Lifted the suspension on {$user->name}", $user);

        return back()->with('status', "{$user->name} can log in again.");
    }

    public function role(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => "You can't change your own role."]);
        }

        $data = $request->validate(['role' => ['required', Rule::in(['student', 'admin'])]]);

        $user->forceFill(['role' => $data['role']])->save();

        ActivityLog::record('user.role_changed', "Changed {$user->name}'s role to {$data['role']}", $user);

        return back()->with('status', "{$user->name} is now " . ($data['role'] === 'admin' ? 'an admin.' : 'a student.'));
    }
}
