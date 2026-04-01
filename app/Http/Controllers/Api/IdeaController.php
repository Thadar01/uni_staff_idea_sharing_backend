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


class IdeaController extends Controller
{
    public function index()
    {
        $ideas = Idea::with([
            'staff',
            'closureSetting',
            'categories',
            'comments.staff',
            'votes',
            'documents'
        ])
        ->where('status', '!=', 'deleted')
        ->get();

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
                    // Create a unique filename
                    $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $fileName);
                    $path = 'idea_documents/' . $fileName;

                    Document::create([
                        'docPath' => $path,
                        'fileType' => $file->getClientOriginalExtension(),
                        'fileSize' => $file->getSize(),
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
        $query->where('status', '!=', 'deleted');
    },
    'comments.staff'
])
->where('status', '!=', 'deleted')
->find($id);

    if (!$idea) {
        return response()->json([
            'success' => false,
            'message' => 'Idea not found',
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

        // overwrite all old documents if new documents are uploaded
        if ($request->hasFile('documents')) {
            $destinationPath = public_path('idea_documents');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            foreach ($idea->documents as $oldDocument) {
                $oldPath = public_path($oldDocument->docPath);
                if ($oldDocument->docPath && file_exists($oldPath)) {
                    unlink($oldPath);
                }
                $oldDocument->delete();
            }

            foreach ($request->file('documents') as $file) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $fileName);
                $path = 'idea_documents/' . $fileName;

                Document::create([
                    'docPath' => $path,
                    'fileType' => $file->getClientOriginalExtension(),
                    'fileSize' => $file->getSize(),
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
}

