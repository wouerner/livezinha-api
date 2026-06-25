<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use Illuminate\Http\Request;

class LiveStreamController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', LiveStream::class);

        return response()->json(LiveStream::orderBy('scheduled_at', 'desc')->get());
    }

    public function store(Request $request)
    {
        $this->authorize('create', LiveStream::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'streamer_name' => 'nullable|string|max:255',
            'live_url' => 'nullable|url|max:2048',
            'scheduled_at' => 'required|date',
        ]);

        $liveStream = LiveStream::create([
            'title' => $validated['title'],
            'streamer_name' => $validated['streamer_name'] ?? null,
            'live_url' => $validated['live_url'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => 'scheduled',
        ]);

        return response()->json($liveStream, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LiveStream $liveStream)
    {
        $this->authorize('view', $liveStream);

        return response()->json($liveStream);
    }

    public function update(Request $request, LiveStream $liveStream)
    {
        $this->authorize('update', $liveStream);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'streamer_name' => 'nullable|string|max:255',
            'live_url' => 'nullable|url|max:2048',
            'scheduled_at' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:scheduled,active,finished',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'active') {
            LiveStream::where('status', 'active')->update(['status' => 'finished']);
            $liveStream->started_at = now();
        }

        $liveStream->update($validated);

        return response()->json($liveStream);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiveStream $liveStream)
    {
        $this->authorize('delete', $liveStream);

        $liveStream->delete();
        return response()->json(['message' => 'Live deletada com sucesso']);
    }

    /**
     * Public listing of all lives (no sensitive data).
     */
    public function publicIndex()
    {
        return response()->json(LiveStream::orderBy('scheduled_at', 'desc')->get());
    }

    /**
     * Get the currently active live, or the next scheduled one if none is active.
     */
    public function activeLive()
    {
        $active = LiveStream::where('status', 'active')->first();
        
        if (!$active) {
            $active = LiveStream::where('status', 'scheduled')
                ->orderBy('scheduled_at', 'asc')
                ->first();
        }

        return response()->json($active);
    }
}
