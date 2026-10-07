<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    private const PER_PAGE = 15;

    /**
     * Search students by username.
     *
     * A normal visit returns the full page. The live-search script asks for the same
     * URL with an X-Requested-With header and gets just the results list back.
     */
    public function index(Request $request): Response
    {
        $raw = $request->query('q');
        $q   = is_string($raw) ? trim(Str::of($raw)->squish()->limit(30, '')->toString()) : '';

        $users = $q === ''
            ? null
            : $this->matchingUsers($q)->simplePaginate(self::PER_PAGE)->withQueryString();

        return response()
            ->view($request->ajax() ? 'search._results' : 'search.index', [
                'q'     => $q,
                'users' => $users,
            ])
            ->header('Vary', 'X-Requested-With'); // so the browser never mixes up the page and the fragment
    }

    /**
     * Usernames that contain the search text, best matches first:
     * exact match, then "starts with", then "contains", then A-Z.
     */
    private function matchingUsers(string $q): Builder
    {
        $term = mb_strtolower($q);

        // Escape LIKE wildcards so searching for "_" or "%" finds those characters, not everyone.
        $like = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);

        return User::query()
            // Only what the results list needs. Never email, birthday or password.
            ->select(['id', 'username', 'role', 'created_at'])
            // Hide suspended accounts (same rule as User::isSuspended()).
            ->where(function (Builder $s) {
                $s->whereNull('suspended_at')->orWhere('suspended_until', '<=', now());
            })
            ->whereRaw("LOWER(username) LIKE ? ESCAPE '!'", ['%' . $like . '%'])
            ->orderByRaw(
                "CASE WHEN LOWER(username) = ? THEN 0 WHEN LOWER(username) LIKE ? ESCAPE '!' THEN 1 ELSE 2 END",
                [$term, $like . '%']
            )
            ->orderBy('username');
    }
}
