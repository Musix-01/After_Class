<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Memory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemoryController extends Controller
{
    private const RULES = [
        'title'     => ['required', 'string', 'max:120'],
        'body'      => ['required', 'string', 'max:5000'],
        'unlock_at' => ['required', 'date'],
        'image'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
    ];

    public function index(): View
    {
        return view('admin.memories.index', [
            'memories' => Memory::orderByDesc('unlock_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.memories.form', ['memory' => new Memory()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(self::RULES);

        $memory = Memory::create([
            'title'      => $data['title'],
            'body'       => $data['body'],
            'unlock_at'  => Carbon::parse($data['unlock_at']),
            'image_path' => $request->hasFile('image') ? $request->file('image')->store('memories', 'public') : null,
            'created_by' => $request->user()->id,
        ]);

        ActivityLog::record('memory.created', "Created memory capsule \"{$memory->title}\" (opens {$memory->unlock_at->format('M j, Y')})", $memory);

        return redirect()->route('admin.memories.index')->with('status', 'Memory capsule sealed. It opens on ' . $memory->unlock_at->format('M j, Y g:i A') . '.');
    }

    public function edit(Memory $memory): View
    {
        return view('admin.memories.form', compact('memory'));
    }

    public function update(Request $request, Memory $memory): RedirectResponse
    {
        $data = $request->validate(self::RULES + ['remove_image' => ['sometimes', 'boolean']]);

        $unlockAt = Carbon::parse($data['unlock_at']);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            $this->deleteImage($memory);
            $memory->image_path = $request->hasFile('image')
                ? $request->file('image')->store('memories', 'public')
                : null;
        }

        // Moving the date re-seals the capsule.
        if (! $unlockAt->equalTo($memory->unlock_at)) {
            $memory->opened_at = null;
        }

        $memory->fill(['title' => $data['title'], 'body' => $data['body'], 'unlock_at' => $unlockAt])->save();

        ActivityLog::record('memory.updated', "Edited memory capsule \"{$memory->title}\"", $memory);

        return redirect()->route('admin.memories.index')->with('status', 'Memory capsule updated.');
    }

    /** Open the capsule right now, before its unlock date. */
    public function open(Memory $memory): RedirectResponse
    {
        if ($memory->isUnlocked()) {
            return back()->with('status', 'That capsule is already open.');
        }

        $memory->forceFill(['opened_at' => now()])->save();

        ActivityLog::record('memory.opened', "Opened memory capsule \"{$memory->title}\" early", $memory);

        return back()->with('status', "\"{$memory->title}\" is now open to everyone.");
    }

    public function destroy(Memory $memory): RedirectResponse
    {
        $this->deleteImage($memory);
        $title = $memory->title;
        $memory->delete();

        ActivityLog::record('memory.deleted', "Deleted memory capsule \"{$title}\"");

        return redirect()->route('admin.memories.index')->with('status', 'Memory capsule deleted.');
    }

    private function deleteImage(Memory $memory): void
    {
        if ($memory->image_path) {
            Storage::disk('public')->delete($memory->image_path);
        }
    }
}
