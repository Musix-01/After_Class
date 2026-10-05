<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** Moderation queue: reported posts waiting for a decision. */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'open'); // open | dismissed | actioned | all

        $reports = Report::query()
            ->with([
                'post' => fn ($p) => $p->withTrashed()->with('user:id,name', 'original.user:id,name'),
                'reporter:id,name',
                'reviewer:id,name',
            ])
            ->when(in_array($status, ['open', 'dismissed', 'actioned'], true), fn ($q) => $q->where('status', $status))
            ->latest()
            ->simplePaginate(10)
            ->withQueryString();

        return view('admin.reports.index', compact('reports', 'status'));
    }

    /** The post is fine: close the report. */
    public function dismiss(Request $request, Report $report): RedirectResponse
    {
        $report->forceFill([
            'status'      => 'dismissed',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ])->save();

        ActivityLog::record('report.dismissed', "Dismissed a report on post #{$report->post_id}", $report);

        return back()->with('status', 'Report dismissed.');
    }

    /** The post breaks the rules: take it down (this also closes every open report on it). */
    public function remove(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:200']], [
            'reason.required' => 'Write a short reason for the audit log.',
        ]);

        $post = $report->post;

        if ($post->trashed()) {
            return back()->withErrors(['report' => 'That post is already removed.']);
        }

        $post->removeByModerator($request->user(), $data['reason']);

        return back()->with('status', 'Post removed and reports closed.');
    }
}
