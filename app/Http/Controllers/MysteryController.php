<?php

namespace App\Http\Controllers;

use App\Models\Mystery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MysteryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = is_string($status) && isset(Mystery::STATUSES[$status]) ? $status : null;

        $mysteries = Mystery::query()
            ->withCount(['clues', 'theories'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('mysteries.index', ['mysteries' => $mysteries, 'status' => $status]);
    }

    public function show(Mystery $mystery): View
    {
        $mystery->load(['clues', 'theories.user:id,name,role']);

        return view('mysteries.show', ['mystery' => $mystery]);
    }
}
