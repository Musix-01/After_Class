<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /** /profile sends you to your own page (used by the sidebar's Profile link). */
    public function me(Request $request): RedirectResponse
    {
        return redirect()->route('users.show', $request->user());
    }

    /** A student's public page: their username and the posts they did NOT make anonymously. */
    public function show(Request $request, User $user): View
    {
        $viewer = $request->user();

        // Suspended accounts look like they don't exist (admins can still open them).
        abort_if($user->isSuspended() && ! $viewer->isAdmin(), 404);

        // Anonymous posts are never listed, otherwise "anonymous" would mean nothing.
        // Deleted/removed posts are skipped automatically (soft deletes).
        $publicPosts = fn () => Post::query()
            ->where('user_id', $user->id)
            ->where('is_anonymous', false);

        return view('users.show', [
            'profile'   => $user,
            'isMe'      => $user->is($viewer),
            'postCount' => $publicPosts()->count(),
            'posts'     => $publicPosts()
                ->forWall($viewer->id)
                ->latest()
                ->latest('id')
                ->simplePaginate(10),
        ]);
    }
}
