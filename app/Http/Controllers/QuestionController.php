<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use App\Models\Question;
use App\Models\QuestionVote;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Question::class);

        $query = Question::withCount([
            'votes as likes_count' => fn ($q) => $q->where('vote', 'like'),
            'votes as dislikes_count' => fn ($q) => $q->where('vote', 'dislike'),
        ]);

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
            'tiktok_handle' => 'nullable|string|max:255|regex:/^@?[a-zA-Z0-9_.]+$/',
            'question_text' => 'required|string|max:1000',
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

        $question->loadCount([
            'votes as likes_count' => fn ($q) => $q->where('vote', 'like'),
            'votes as dislikes_count' => fn ($q) => $q->where('vote', 'dislike'),
        ]);

        return response()->json($question, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        $this->authorize('view', $question);

        $question->loadCount([
            'votes as likes_count' => fn ($q) => $q->where('vote', 'like'),
            'votes as dislikes_count' => fn ($q) => $q->where('vote', 'dislike'),
        ]);

        return response()->json($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $this->authorize('update', $question);

        $validated = $request->validate([
            'status' => 'sometimes|required|in:pending,approved,active,archived',
            'is_tagged' => 'sometimes|required|boolean',
            'is_hidden' => 'sometimes|required|boolean',
        ]);
 
        if (isset($validated['status'])) {
            $now = now();
 
            if ($validated['status'] === 'active') {
                // Get currently active questions for this live stream, ordered by displayed_at ascending (oldest first)
                $currentlyActive = Question::where('live_stream_id', $question->live_stream_id)
                    ->where('status', 'active')
                    ->orderBy('displayed_at', 'asc')
                    ->get();
 
                if ($currentlyActive->count() >= 3) {
                    $oldest = $currentlyActive->first();
                    $displayedAt = $oldest->displayed_at ? \Carbon\Carbon::parse($oldest->displayed_at) : null;
                    $duration = $displayedAt ? $now->diffInSeconds($displayedAt) : null;
 
                    $oldest->update([
                        'status' => 'archived',
                        'removed_at' => $now,
                        'duration_seconds' => $duration,
                    ]);
                }
 
                $validated['displayed_at'] = $now;
                $validated['removed_at'] = null;
                $validated['duration_seconds'] = null;
            } elseif ($question->status === 'active' && $validated['status'] !== 'active') {
                // If this question was active and is now being deactivated
                $displayedAt = $question->displayed_at ? \Carbon\Carbon::parse($question->displayed_at) : null;
                $duration = $displayedAt ? $now->diffInSeconds($displayedAt) : null;
 
                $validated['removed_at'] = $now;
                $validated['duration_seconds'] = $duration;
            }
        }
 
        $question->update($validated);

        $question->loadCount([
            'votes as likes_count' => fn ($q) => $q->where('vote', 'like'),
            'votes as dislikes_count' => fn ($q) => $q->where('vote', 'dislike'),
        ]);

        return response()->json($question);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $this->authorize('delete', $question);

        $question->delete();
        return response()->json(['message' => 'Pergunta excluída com sucesso']);
    }

    public function vote(Request $request, Question $question)
    {
        $validated = $request->validate([
            'vote' => 'required|in:like,dislike',
        ]);

        $ip = $request->ip();

        $existingVote = QuestionVote::where('question_id', $question->id)
            ->where('voter_ip', $ip)
            ->first();

        if ($existingVote) {
            if ($existingVote->created_at->gt(now()->subHour())) {
                return response()->json([
                    'message' => 'Aguarde 1 hora para votar novamente nesta pergunta.',
                ], 429);
            }

            $existingVote->update(['vote' => $validated['vote']]);
        } else {
            QuestionVote::create([
                'question_id' => $question->id,
                'vote' => $validated['vote'],
                'voter_ip' => $ip,
            ]);
        }

        $likesCount = QuestionVote::where('question_id', $question->id)
            ->where('vote', 'like')
            ->count();
        $dislikesCount = QuestionVote::where('question_id', $question->id)
            ->where('vote', 'dislike')
            ->count();

        return response()->json([
            'likes_count' => $likesCount,
            'dislikes_count' => $dislikesCount,
        ]);
    }

    /**
     * Get the currently active question for the active live stream.
     */
    public function activeQuestion()
    {
        // Find active live stream first
        $activeLive = LiveStream::where('status', 'active')->first();
        if (!$activeLive) {
            return response()->json([]);
        }
 
        $activeQuestions = Question::withCount([
                'votes as likes_count' => fn ($q) => $q->where('vote', 'like'),
                'votes as dislikes_count' => fn ($q) => $q->where('vote', 'dislike'),
            ])
            ->where('live_stream_id', $activeLive->id)
            ->where('status', 'active')
            ->where('is_hidden', false)
            ->orderBy('displayed_at', 'asc')
            ->take(3)
            ->get();
 
        return response()->json($activeQuestions);
    }

    /**
     * Get public visible questions for a live stream.
     */
    public function publicQuestions(LiveStream $liveStream)
    {
        $questions = Question::withCount([
                'votes as likes_count' => fn ($q) => $q->where('vote', 'like'),
                'votes as dislikes_count' => fn ($q) => $q->where('vote', 'dislike'),
            ])
            ->where('live_stream_id', $liveStream->id)
            ->whereIn('status', ['approved', 'active'])
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($questions);
    }
}
