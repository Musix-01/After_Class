<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use Illuminate\View\View;

class MemoryController extends Controller
{
    /** Time capsules: open ones first, locked ones counting down. */
    public function index(): View
    {
        $all = Memory::all();

        return view('memories.index', [
            'opened' => $all->filter(fn ($m) => $m->isUnlocked())->sortByDesc('unlock_at')->values(),
            'locked' => $all->reject(fn ($m) => $m->isUnlocked())->sortBy('unlock_at')->values(),
        ]);
    }

    public function show(Memory $memory): View
    {
        return view('memories.show', ['memory' => $memory]);
    }
}
