<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Mystery;
use App\Models\MysteryTheory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TheoryController extends Controller
{
    /** Any student can propose a theory while a mystery is still open. */
    public function store(Request $request, Mystery $mystery): RedirectResponse
    {
        abort_unless($mystery->isOpen(), 403, 'This mystery is closed.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ], [
            'body.required' => 'Write your theory first.',
            'body.max'      => 'Theories can be up to 1,000 characters.',
        ]);

        $theory = MysteryTheory::create([
            'mystery_id' => $mystery->id,
            'user_id'    => $request->user()->id,
            'body'       => $data['body'],
        ]);

        ActivityLog::record(
            'mystery.theory',
            "{$request->user()->name} added a theory to \"{$mystery->title}\"",
            $theory,
            ['mystery_id' => $mystery->id],
            true
        );

        return back()->with('status', 'Theory added.')->withFragment('theories');
    }

    /** The author of a theory, or an admin, can remove it. */
    public function destroy(Request $request, MysteryTheory $theory): RedirectResponse
    {
        abort_unless(
            $request->user()->isAdmin() || (int) $theory->user_id === (int) $request->user()->id,
            403
        );

        $theory->delete();

        ActivityLog::record('mystery.theory_removed', "{$request->user()->name} removed a theory", null, ['mystery_id' => $theory->mystery_id]);

        return back()->with('status', 'Theory removed.')->withFragment('theories');
    }
}
