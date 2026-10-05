<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /** A student flags a post for the moderators. */
    public function store(Request $request, Post $post): RedirectResponse
    {
        if ($post->isOwnedBy($request->user()->id)) {
            return back()->withErrors(['report' => "You can't report your own post."])->withFragment('post-' . $post->id);
        }

        $data = $request->validate([
            'reason'  => ['required', Rule::in(array_keys(Report::REASONS))],
            'details' => ['nullable', 'string', 'max:500'],
        ], [
            'reason.required' => 'Pick a reason for your report.',
        ]);

        $report = Report::firstOrNew(['post_id' => $post->id, 'user_id' => $request->user()->id]);

        if ($report->exists) {
            return back()->with('status', 'You already reported this post. A moderator will review it.')
                ->withFragment('post-' . $post->id);
        }

        $report->fill([
            'reason'  => $data['reason'],
            'details' => $data['details'] ?? null,
            'status'  => 'open',
        ])->save();

        ActivityLog::record(
            'report.created',
            "{$request->user()->name} reported post #{$post->id} ({$report->reason_label})",
            $report,
            ['post_id' => $post->id],
            true // shows up in admin notifications
        );

        return back()->with('status', 'Thanks. A moderator will take a look.')->withFragment('post-' . $post->id);
    }
}
