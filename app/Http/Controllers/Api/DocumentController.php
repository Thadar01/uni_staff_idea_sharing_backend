<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with('idea')->get();

        return response()->json([
            'success' => true,
            'message' => $documents->isEmpty() ? 'No documents found' : 'Documents retrieved successfully',
            'data' => $documents
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'docPath' => 'required|string|max:255',
                'ideaID' => 'required|integer|exists:idea,ideaID',
            ]);

            $document = Document::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Document created successfully',
                'data' => $document
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
        $document = Document::with('idea')->find($id);

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document retrieved successfully',
            'data' => $document
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $document = Document::find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'docPath' => 'required|string|max:255',
                'ideaID' => 'required|integer|exists:idea,ideaID',
            ]);

            $document->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Document updated successfully',
                'data' => $document
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
            $document = Document::find($id);

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found',
                    'data' => null
                ], 404);
            }

            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
                'data' => null
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
