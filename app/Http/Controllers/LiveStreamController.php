<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use Illuminate\Http\Request;

class LiveStreamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(LiveStream::orderBy('scheduled_at', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'scheduled_at' => 'required|date',
        ]);

        $liveStream = LiveStream::create([
            'title' => $validated['title'],
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
        return response()->json($liveStream);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LiveStream $liveStream)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'scheduled_at' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:scheduled,active,finished',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'active') {
            // Deactivate all other active lives
            LiveStream::where('status', 'active')->update(['status' => 'finished']);
        }

        $liveStream->update($validated);

        return response()->json($liveStream);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LiveStream $liveStream)
    {
        $liveStream->delete();
        return response()->json(['message' => 'Live deletada com sucesso']);
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
