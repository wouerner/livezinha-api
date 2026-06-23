<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Question::query();

        if ($request->has('live_stream_id')) {
            $query->where('live_stream_id', $request->live_stream_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'live_stream_id' => 'required|exists:live_streams,id',
            'name' => 'required|string|max:255',
            'tiktok_handle' => 'nullable|string|max:255',
            'question_text' => 'required|string',
        ]);

        // Clean up tiktok handle (ensure it has @ prefix if not empty)
        $tiktokHandle = $validated['tiktok_handle'];
        if ($tiktokHandle && !str_starts_with($tiktokHandle, '@')) {
            $tiktokHandle = '@' . $tiktokHandle;
        }

        $question = Question::create([
            'live_stream_id' => $validated['live_stream_id'],
            'name' => $validated['name'],
            'tiktok_handle' => $tiktokHandle,
            'question_text' => $validated['question_text'],
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'pending',
            'is_tagged' => false,
        ]);

        return response()->json($question, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        return response()->json($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'status' => 'sometimes|required|in:pending,approved,active,archived',
            'is_tagged' => 'sometimes|required|boolean',
            'is_hidden' => 'sometimes|required|boolean',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'active') {
            // Archive any other active questions for this live stream
            Question::where('live_stream_id', $question->live_stream_id)
                ->where('status', 'active')
                ->update(['status' => 'archived']);
        }

        $question->update($validated);

        return response()->json($question);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $question->delete();
        return response()->json(['message' => 'Pergunta excluída com sucesso']);
    }

    /**
     * Get the currently active question for the active live stream.
     */
    public function activeQuestion()
    {
        // Find active live stream first
        $activeLive = LiveStream::where('status', 'active')->first();
        if (!$activeLive) {
            return response()->json(null);
        }

        $activeQuestion = Question::where('live_stream_id', $activeLive->id)
            ->where('status', 'active')
            ->first();

        return response()->json($activeQuestion);
    }

    /**
     * Get public visible questions for a live stream.
     */
    public function publicQuestions(LiveStream $liveStream)
    {
        $questions = Question::where('live_stream_id', $liveStream->id)
            ->whereIn('status', ['approved', 'active'])
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($questions);
    }
}
