<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Idea;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Staff;
use App\Mail\NewIdeaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class IdeaController extends Controller
{
    public function index()
    {
        $ideas = Idea::with([
            'staff',
            'closureSetting',
            'categories',
            'comments' => function ($query) {
                $query->where('status', 'active')->latest()->with('staff');
            },
            'votes',
            'documents'
        ])
            ->whereNotIn('status', ['deleted', 'hidden'])
            ->latest()
            ->paginate(5);

        return response()->json([
            'success' => true,
            'message' => $ideas->isEmpty() ? 'No ideas found' : 'Ideas retrieved successfully',
            'data' => $ideas
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'isAnonymous' => 'nullable|boolean',
                'staffID' => 'required|integer|exists:staffs,staffID',
                'status' => 'nullable|string|in:pending,approved,rejected,deleted',
                'viewCount' => 'nullable|integer|min:0',
                'isFlagged' => 'nullable|boolean',
                'isCommentEnabled' => 'nullable|boolean',

                'categoryIDs' => 'nullable|array',
                'categoryIDs.*' => 'integer|exists:categories,categoryID',

                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
            ]);

            DB::beginTransaction();

            $idea = Idea::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'isAnonymous' => $validated['isAnonymous'] ?? false,
                'staffID' => $validated['staffID'],
                'status' => $validated['status'] ?? 'pending',
                'viewCount' => $validated['viewCount'] ?? 0,
                'isFlagged' => $validated['isFlagged'] ?? false,
                'isCommentEnabled' => $validated['isCommentEnabled'] ?? true,
            ]);

            if (!empty($validated['categoryIDs'])) {
                $uniqueCategoryIDs = array_unique($validated['categoryIDs']);
                $idea->categories()->attach($uniqueCategoryIDs);
            }

            if ($request->hasFile('documents')) {
                $destinationPath = public_path('idea_documents');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                foreach ($request->file('documents') as $file) {
                    // Capture file info BEFORE moving the file to prevent "stat failed" error
                    $originalName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    $fileExtension = $file->getClientOriginalExtension();

                    // Create a filename using current time and original name
                    $fileName = time() . '_' . $originalName;
                    $file->move($destinationPath, $fileName);
                    $path = 'idea_documents/' . $fileName;

                    Document::create([
                        'docPath' => $path,
                        'fileType' => $fileExtension,
                        'fileSize' => $fileSize,
                        'isHidden' => false,
                        'ideaID' => $idea->ideaID,
                    ]);
                }
            }

            DB::commit();

            $idea->load([
                'staff',
                'closureSetting',
                'categories',
                'comments.staff',
                'votes',
                'documents'
            ]);

            // --- Email Notification Dispatching ---
            try {
                // 1. Get QA Managers
                $qaManagers = Staff::whereHas('role', function ($q) {
                    $q->where('roleName', 'QA Manager');
                })->whereNotNull('staffEmail')->pluck('staffEmail')->toArray();

                // 2. Get QA Coordinators within the same department
                $qaCoordinators = Staff::whereHas('role', function ($q) {
                    $q->where('roleName', 'QA Coordinator');
                })->where('departmentID', $idea->staff ? $idea->staff->departmentID : null)
                    ->whereNotNull('staffEmail')
                    ->pluck('staffEmail')->toArray();

                $recipients = array_unique(array_filter(array_merge($qaManagers, $qaCoordinators)));

                if (!empty($recipients)) {
                    Mail::to($recipients)->send(new NewIdeaMail($idea));
                }
            } catch (\Exception $e) {
                // Log and silently catch so it doesn't break the idea creation API
                Log::error('Failed to send New Idea email: ' . $e->getMessage());
            }
            // --------------------------------

            return response()->json([
                'success' => true,
                'message' => 'Idea created successfully',
                'data' => $idea
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);

        } catch (QueryException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred',
                'data' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
    {
        $idea = Idea::with([
            'staff',
            'closureSetting',
            'categories',
            'votes',
            'documents',
            'comments' => function ($query) {
                $query->where('status', 'active')->latest()->with('staff');
            }
        ])
            ->whereNotIn('status', ['deleted', 'hidden'])
            ->find($id);

        if (!$idea) {
            return response()->json([
                'success' => false,
                'message' => 'Idea not found or hidden',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Idea retrieved successfully',
            'data' => $idea
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $idea = Idea::with('documents')->find($id);

            if (!$idea || $idea->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'isAnonymous' => 'nullable|boolean',
                'staffID' => 'sometimes|required|integer|exists:staffs,staffID',
                'settingID' => 'nullable|integer|exists:closure_setting,settingID',
                'status' => 'nullable|string|in:pending,approved,rejected,deleted',
                'viewCount' => 'nullable|integer|min:0',
                'isFlagged' => 'nullable|boolean',
                'isCommentEnabled' => 'nullable|boolean',

                'categoryIDs' => 'nullable|array',
                'categoryIDs.*' => 'integer|distinct|exists:categories,categoryID',

                'documents' => 'nullable|array',
                'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120'
            ]);

            DB::beginTransaction();

            $idea->update([
                'title' => $validated['title'] ?? $idea->title,
                'description' => $validated['description'] ?? $idea->description,
                'isAnonymous' => $validated['isAnonymous'] ?? $idea->isAnonymous,
                'staffID' => $validated['staffID'] ?? $idea->staffID,
                'settingID' => array_key_exists('settingID', $validated) ? $validated['settingID'] : $idea->settingID,
                'status' => $validated['status'] ?? $idea->status,
                'viewCount' => $validated['viewCount'] ?? $idea->viewCount,
                'isFlagged' => $validated['isFlagged'] ?? $idea->isFlagged,
                'isCommentEnabled' => $validated['isCommentEnabled'] ?? $idea->isCommentEnabled,
            ]);

            if ($request->has('categoryIDs')) {
                $uniqueCategoryIDs = array_values(array_unique($validated['categoryIDs'] ?? []));
                $idea->categories()->sync($uniqueCategoryIDs);
            }

            // Append new documents if uploaded
            if ($request->hasFile('documents')) {
                $destinationPath = public_path('idea_documents');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                foreach ($request->file('documents') as $file) {
                    // Capture file info BEFORE moving the file to prevent "stat failed" error
                    $originalName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    $fileExtension = $file->getClientOriginalExtension();

                    // Create a filename using current time and original name
                    $fileName = time() . '_' . $originalName;
                    $file->move($destinationPath, $fileName);
                    $path = 'idea_documents/' . $fileName;

                    Document::create([
                        'docPath' => $path,
                        'fileType' => $fileExtension,
                        'fileSize' => $fileSize,
                        'isHidden' => false,
                        'ideaID' => $idea->ideaID,
                    ]);
                }
            }

            DB::commit();

            $idea->load([
                'staff',
                'closureSetting',
                'categories',
                'comments.staff',
                'votes',
                'documents'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Idea updated successfully',
                'data' => $idea
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred',
                'data' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(Request $request, $id)
    {
        try {
            $idea = Idea::find($id);

            if (!$idea || $idea->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'status' => 'required|string|in:approved,rejected',
                'settingID' => 'nullable|integer|exists:closure_setting,settingID',
            ]);

            if ($validated['status'] === 'approved' && empty($validated['settingID'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'settingID is required when approving an idea',
                    'data' => null
                ], 422);
            }

            $idea->status = $validated['status'];

            if ($validated['status'] === 'approved') {
                $idea->settingID = $validated['settingID'];
            }

            if ($validated['status'] === 'rejected') {
                $idea->settingID = null;
            }

            $idea->save();

            $idea->load([
                'staff',
                'closureSetting',
                'categories',
                'comments.staff',
                'votes',
                'documents'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Idea status updated successfully',
                'data' => $idea
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

    public function updateOnlyStatus(Request $request, $id)
    {
        try {
            $idea = Idea::find($id);

            if (!$idea || $idea->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'status' => 'required|string|in:pending,approved,rejected'
            ]);

            $idea->status = $validated['status'];
            $idea->save();

            $idea->load([
                'staff',
                'closureSetting',
                'categories',
                'comments.staff',
                'votes',
                'documents'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Idea status updated successfully',
                'data' => $idea
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
    public function increaseViewCount($id)
    {
        try {
            $idea = Idea::find($id);

            if (!$idea || $idea->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $idea->increment('viewCount');
            $idea->refresh();

            return response()->json([
                'success' => true,
                'message' => 'View count increased successfully',
                'data' => $idea
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to increase view count',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $idea = Idea::find($id);

            if (!$idea) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $idea->status = 'deleted';
            $idea->save();

            return response()->json([
                'success' => true,
                'message' => 'Idea deleted successfully',
                'data' => $idea
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete idea',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function hide($id)
    {
        try {
            $idea = Idea::find($id);

            if (!$idea || $idea->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $idea->status = 'hidden';
            $idea->save();

            return response()->json([
                'success' => true,
                'message' => 'Idea hidden successfully',
                'data' => $idea
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to hide idea',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function unhide($id)
    {
        try {
            $idea = Idea::find($id);

            if (!$idea || $idea->status === 'deleted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Idea not found',
                    'data' => null
                ], 404);
            }

            $idea->status = 'approved';
            $idea->save();

            return response()->json([
                'success' => true,
                'message' => 'Idea unhidden successfully',
                'data' => $idea
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unhide idea',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function syncClosureStatuses()
    {
        try {
            $today = now()->format('Y-m-d');

            // Find settings where final closure date has passed
            $expiredSettingIDs = DB::table('closure_setting')
                ->where('finalclosureDate', '<', $today)
                ->pluck('settingID');

            if ($expiredSettingIDs->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No expired closure settings found. No ideas updated.',
                    'data' => 0
                ], 200);
            }

            // Update ideas to inactive
            $updatedCount = Idea::whereIn('settingID', $expiredSettingIDs)
                ->whereNotIn('status', ['deleted', 'inactive'])
                ->update(['status' => 'inactive']);

            return response()->json([
                'success' => true,
                'message' => 'Idea statuses synchronized successfully.',
                'data' => $updatedCount
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to synchronize idea statuses',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}

