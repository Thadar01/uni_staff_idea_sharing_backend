<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['reporter', 'resolver', 'idea', 'comment'])->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reports found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reports retrieved successfully',
            'data' => $reports
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'report_type' => 'required|in:idea,comment',
                'reason' => 'required|string',
                'ideaID' => 'nullable|required_if:report_type,idea|exists:idea,ideaID',
                'commentID' => 'nullable|required_if:report_type,comment|exists:comments,commentID',
                'reporter_id' => 'required|exists:staffs,staffID',
                'resolved_by' => 'nullable|exists:staffs,staffID',
                'status' => 'nullable|in:pending,resolved,dismissed',
            ]);

            if ($validated['report_type'] === 'idea') {
                $validated['commentID'] = null;
            }

            if ($validated['report_type'] === 'comment') {
                $validated['ideaID'] = null;
            }

            if (!isset($validated['status'])) {
                $validated['status'] = 'pending';
            }

            $report = Report::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Report created successfully',
                'data' => $report->load(['reporter', 'resolver', 'idea', 'comment'])
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
        $report = Report::with(['reporter', 'resolver', 'idea', 'comment'])->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Report not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Report retrieved successfully',
            'data' => $report
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $report = Report::find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'report_type' => 'required|in:idea,comment',
                'reason' => 'required|string',
                'ideaID' => 'nullable|required_if:report_type,idea|exists:idea,ideaID',
                'commentID' => 'nullable|required_if:report_type,comment|exists:comments,commentID',
                'reporter_id' => 'required|exists:staffs,staffID',
                'resolved_by' => 'nullable|exists:staffs,staffID',
                'status' => 'required|in:pending,resolved,dismissed',
            ]);

            if ($validated['report_type'] === 'idea') {
                $validated['commentID'] = null;
            }

            if ($validated['report_type'] === 'comment') {
                $validated['ideaID'] = null;
            }

            $report->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully',
                'data' => $report->fresh()->load(['reporter', 'resolver', 'idea', 'comment'])
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

  
}