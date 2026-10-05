<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    private const DAYS = 14;

    public function index(): View
    {
        $weekAgo = now()->subDays(7);

        $totals = [
            'users'    => User::count(),
            'posts'    => Post::count(),
            'comments' => DB::table('comments')->count(),
            'likes'    => DB::table('post_likes')->count(),
            'reposts'  => Post::whereNotNull('original_post_id')->count(),
            'active'   => ActivityLog::where('action', 'auth.login')->where('created_at', '>=', $weekAgo)
                ->distinct()->count('user_id'),
        ];

        return view('admin.analytics.index', [
            'totals'   => $totals,
            'days'     => self::DAYS,
            'newUsers' => $this->daily('users'),
            'newPosts' => $this->daily('posts'),
            'newComments' => $this->daily('comments'),

            'byCategory' => collect(Post::CATEGORIES)->map(
                fn ($label, $key) => Post::where('category', $key)->count()
            )->mapWithKeys(fn ($count, $key) => [Post::CATEGORIES[$key] => $count]),

            'topPosts' => Post::with('user:id,name')
                ->withCount(['likes', 'comments', 'reposts'])
                ->orderByDesc('likes_count')->orderByDesc('comments_count')
                ->limit(5)->get()->filter(fn ($p) => $p->likes_count + $p->comments_count > 0),

            'topUsers' => User::withCount(['posts' => fn ($q) => $q->where('created_at', '>=', now()->subDays(30))])
                ->orderByDesc('posts_count')->limit(5)->get()->filter(fn ($u) => $u->posts_count > 0),

            'reportsByReason' => Report::select('reason', DB::raw('COUNT(*) as total'))
                ->groupBy('reason')->orderByDesc('total')->pluck('total', 'reason'),

            'reportStats' => [
                'open'      => Report::where('status', 'open')->count(),
                'actioned'  => Report::where('status', 'actioned')->count(),
                'dismissed' => Report::where('status', 'dismissed')->count(),
            ],
        ]);
    }

    /** Rows created per day for the last N days, zero-filled. Returns ['Oct 4' => 3, ...]. */
    private function daily(string $table): array
    {
        $start = now()->subDays(self::DAYS - 1)->startOfDay();

        $rows = DB::table($table)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd');

        $series = [];
        for ($i = 0; $i < self::DAYS; $i++) {
            $day = $start->copy()->addDays($i);
            $series[$day->format('M j')] = (int) ($rows[$day->toDateString()] ?? 0);
        }

        return $series;
    }
}
