<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class VoteController extends Controller
{
    public function index()
    {
        $votes = Vote::with(['staff', 'idea'])->get();

        return response()->json([
            'success' => true,
            'message' => $votes->isEmpty() ? 'No votes found' : 'Votes retrieved successfully',
            'data' => $votes
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'voteType' => 'required|in:Like,Unlike',
                'staffID' => 'required|integer|exists:staffs,staffID',
                'ideaID' => 'required|integer|exists:idea,ideaID',
            ]);

            // Prevent duplicate vote
            $exists = Vote::where('staffID', $validated['staffID'])
                          ->where('ideaID', $validated['ideaID'])
                          ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This staff has already voted for this idea',
                    'data' => null
                ], 409);
            }

            $vote = Vote::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Vote recorded successfully',
                'data' => $vote
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'data' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $vote = Vote::with(['staff', 'idea'])->find($id);

        if (!$vote) {
            return response()->json([
                'success' => false,
                'message' => 'Vote not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Vote retrieved successfully',
            'data' => $vote
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $vote = Vote::find($id);

            if (!$vote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vote not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'voteType' => 'required|in:Like,Unlike',
                'staffID' => 'required|integer|exists:staffs,staffID',
                'ideaID' => 'required|integer|exists:idea,ideaID',
            ]);

            // Prevent duplicate vote on update
            $exists = Vote::where('staffID', $validated['staffID'])
                          ->where('ideaID', $validated['ideaID'])
                          ->where('voteID', '!=', $id)
                          ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This staff has already voted for this idea',
                    'data' => null
                ], 409);
            }

            $vote->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Vote updated successfully',
                'data' => $vote
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $vote = Vote::find($id);

            if (!$vote) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vote not found',
                    'data' => null
                ], 404);
            }

            $vote->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vote deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete vote',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
