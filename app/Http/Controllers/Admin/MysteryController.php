<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Mystery;
use App\Models\MysteryClue;
use App\Models\MysteryTheory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MysteryController extends Controller
{
    private const RULES = [
        'title'    => ['required', 'string', 'max:140'],
        'summary'  => ['required', 'string', 'max:3000'],
        'location' => ['nullable', 'string', 'max:120'],
    ];

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $mysteries = Mystery::query()
            ->withCount(['clues', 'theories'])
            ->when(isset(Mystery::STATUSES[$status]), fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        return view('admin.mysteries.index', compact('mysteries', 'status'));
    }

    public function create(): View
    {
        return view('admin.mysteries.form', ['mystery' => new Mystery()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(self::RULES);

        $mystery = Mystery::create($data + ['status' => 'open', 'created_by' => $request->user()->id]);

        ActivityLog::record('mystery.created', "Opened a new mystery: \"{$mystery->title}\"", $mystery);

        return redirect()->route('admin.mysteries.show', $mystery)->with('status', 'Mystery created. Add the first clue below.');
    }

    /** The investigation desk: clues, theories and the verdict. */
    public function show(Mystery $mystery): View
    {
        $mystery->load(['clues', 'theories.user:id,name']);

        return view('admin.mysteries.show', compact('mystery'));
    }

    public function edit(Mystery $mystery): View
    {
        return view('admin.mysteries.form', compact('mystery'));
    }

    public function update(Request $request, Mystery $mystery): RedirectResponse
    {
        $mystery->update($request->validate(self::RULES));

        ActivityLog::record('mystery.updated', "Edited mystery \"{$mystery->title}\"", $mystery);

        return redirect()->route('admin.mysteries.show', $mystery)->with('status', 'Mystery updated.');
    }

    public function destroy(Mystery $mystery): RedirectResponse
    {
        $title = $mystery->title;
        $mystery->delete(); // clues and theories cascade

        ActivityLog::record('mystery.deleted', "Deleted mystery \"{$title}\"");

        return redirect()->route('admin.mysteries.index')->with('status', 'Mystery deleted.');
    }

    public function addClue(Request $request, Mystery $mystery): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:1000']], [
            'body.required' => 'Write the clue first.',
        ]);

        MysteryClue::create(['mystery_id' => $mystery->id] + $data);

        ActivityLog::record('mystery.clue_added', "Added a clue to \"{$mystery->title}\"", $mystery);

        return back()->with('status', 'Clue added.')->withFragment('clues');
    }

    public function destroyClue(MysteryClue $clue): RedirectResponse
    {
        $clue->delete();

        return back()->with('status', 'Clue removed.')->withFragment('clues');
    }

    /** Close the case: solved (optionally crediting a theory) or debunked. */
    public function resolve(Request $request, Mystery $mystery): RedirectResponse
    {
        $data = $request->validate([
            'outcome'            => ['required', Rule::in(['solved', 'debunked'])],
            'resolution'         => ['required', 'string', 'max:3000'],
            'accepted_theory_id' => ['nullable', Rule::exists('mystery_theories', 'id')->where('mystery_id', $mystery->id)],
        ], [
            'outcome.required'    => 'Choose solved or debunked.',
            'resolution.required' => 'Explain what really happened.',
        ]);

        MysteryTheory::where('mystery_id', $mystery->id)->update(['is_accepted' => false]);

        if ($data['outcome'] === 'solved' && ! empty($data['accepted_theory_id'])) {
            MysteryTheory::whereKey($data['accepted_theory_id'])->update(['is_accepted' => true]);
        }

        $mystery->forceFill([
            'status'      => $data['outcome'],
            'resolution'  => $data['resolution'],
            'resolved_at' => now(),
        ])->save();

        ActivityLog::record("mystery.{$data['outcome']}", ucfirst($data['outcome']) . " the mystery \"{$mystery->title}\"", $mystery);

        return back()->with('status', 'Case closed: marked as ' . $data['outcome'] . '.');
    }

    public function reopen(Mystery $mystery): RedirectResponse
    {
        MysteryTheory::where('mystery_id', $mystery->id)->update(['is_accepted' => false]);
        $mystery->forceFill(['status' => 'open', 'resolution' => null, 'resolved_at' => null])->save();

        ActivityLog::record('mystery.reopened', "Reopened the mystery \"{$mystery->title}\"", $mystery);

        return back()->with('status', 'Mystery reopened for theories.');
    }
}
