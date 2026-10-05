<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LikeController extends Controller
{
    /** Like the post, or take the like back if it was already liked. */
    public function toggle(Request $request, Post $post): JsonResponse|RedirectResponse
    {
        $result = $post->likes()->toggle($request->user()->id);

        if ($request->expectsJson()) {
            return response()->json([
                'liked' => count($result['attached']) > 0,
                'count' => $post->likes()->count(),
            ]);
        }

        return back()->withFragment('post-' . $post->id);
    }
}
