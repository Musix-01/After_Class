<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /** The Freedom Wall: announcements, composer, then everyone's posts (pinned first). */
    public function index(Request $request): View
    {
        $userId   = $request->user()->id;
        $category = $request->query('category');
        $category = is_string($category) && isset(Post::CATEGORIES[$category]) ? $category : null;
        $mine     = $request->boolean('mine');

        $posts = Post::query()
            ->forWall($userId)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($mine, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('is_pinned')
            ->latest()
            ->latest('id')
            ->simplePaginate(10)
            ->withQueryString();

        return view('home', [
            'posts'          => $posts,
            'activeCategory' => $category,
            'mine'           => $mine,
            'announcements'  => Announcement::active()->latest()->take(3)->get(),
        ]);
    }
}
