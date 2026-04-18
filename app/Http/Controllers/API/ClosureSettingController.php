<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ClosureSetting;
use App\Models\Idea;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IdeaExport;

class ClosureSettingController extends Controller
{
    public function index()
    {
        $closureSettings = ClosureSetting::all();

        return response()->json([
            'success' => true,
            'message' => 'Closure settings retrieved successfully',
            'data' => $closureSettings
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'closureDate' => 'required|date',
                'finalclosureDate' => 'required|date|after_or_equal:closureDate',
                'academicYear' => 'required|string|max:50'
            ]);

            $closureSetting = ClosureSetting::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Closure setting created successfully',
                'data' => $closureSetting
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
        $closureSetting = ClosureSetting::find($id);

        if (!$closureSetting) {
            return response()->json([
                'success' => false,
                'message' => 'Closure setting not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Closure setting retrieved successfully',
            'data' => $closureSetting
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $closureSetting = ClosureSetting::find($id);

            if (!$closureSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closure setting not found',
                    'data' => null
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'closureDate' => 'required|date',
                'finalclosureDate' => 'required|date|after_or_equal:closureDate',
                'academicYear' => 'required|string|max:50'
            ]);

            $closureSetting->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Closure setting updated successfully',
                'data' => $closureSetting
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
            $closureSetting = ClosureSetting::find($id);

            if (!$closureSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closure setting not found',
                    'data' => null
                ], 404);
            }

            // --- MANDATORY CONSTRAINT ---
            // Prevent deactivation if the setting has ideas of its own AND the academic cycle is still active
            $hasIdeas = $closureSetting->ideas()->exists();
            $isActive = Carbon::parse($closureSetting->finalclosureDate)->isFuture() ||
                Carbon::parse($closureSetting->finalclosureDate)->isToday();

            if ($hasIdeas && $isActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot deactivate closure setting. It has associated ideas and the academic cycle is still active.',
                    'data' => null
                ], 403);
            }
            // ---------------------------

            $closureSetting->update([
                'status' => 'inactive'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Closure setting status updated to inactive successfully',
                'data' => $closureSetting->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update closure setting status',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadZip($id)
    {
        try {
            $closureSetting = ClosureSetting::find($id);

            if (!$closureSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closure setting not found',
                    'data' => null
                ], 404);
            }

            // Check if closure date has passed
            $closureDate = Carbon::parse($closureSetting->closureDate);
            if (now()->lt($closureDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documents can only be downloaded after the closure date (' . $closureDate->toDateTimeString() . ')',
                    'data' => null
                ], 403);
            }

            $ideas = Idea::with('documents')
                ->where('settingID', $id)
                ->where('status', 'approved')
                ->get();

            if ($ideas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No approved ideas found for this closure',
                    'data' => null
                ], 404);
            }

            $downloadsDir = public_path('downloads');
            if (!file_exists($downloadsDir)) {
                mkdir($downloadsDir, 0755, true);
            }

            $zipFileName = 'closure_' . $id . '_documents_' . now()->format('YmdHis') . '.zip';
            $zipPath = $downloadsDir . '/' . $zipFileName;

            $zip = new ZipArchive;
            $filesAdded = 0;

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($ideas as $idea) {
                    foreach ($idea->documents as $document) {
                        $filePath = public_path($document->docPath);

                        // Fallback for files uploaded before the storage refactor
                        if (!file_exists($filePath)) {
                            $filePath = storage_path('app/public/' . $document->docPath);
                        }

                        if (file_exists($filePath)) {
                            // Use a unique name within the zip to avoid collisions
                            $nameInZip = 'Idea_' . $idea->ideaID . '/' . basename($document->docPath);
                            $zip->addFile($filePath, $nameInZip);
                            $filesAdded++;
                        }
                    }
                }
                $zip->close();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create zip file',
                    'data' => null
                ], 500);
            }

            if ($filesAdded === 0) {
                if (file_exists($zipPath)) {
                    unlink($zipPath);
                }
                return response()->json([
                    'success' => false,
                    'message' => 'No actual document files were found on the server to zip',
                    'data' => null
                ], 404);
            }

            if (!file_exists($zipPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Zip file could not be found after creation',
                    'data' => null
                ], 500);
            }

            $downloadUrl = url('downloads/' . $zipFileName);

            return response()->json([
                'success' => true,
                'message' => 'Zip file created successfully',
                'data' => [
                    'downloadUrl' => $downloadUrl
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unexpected error occurred',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function exportIdeas(Request $request, $id)
    {
        try {
            $closureSetting = ClosureSetting::find($id);

            if (!$closureSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Closure setting not found',
                    'data' => null
                ], 404);
            }

            // Check if closure date has passed
            $closureDate = Carbon::parse($closureSetting->closureDate);
            if (now()->lt($closureDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ideas can only be exported after the closure date (' . $closureDate->toDateTimeString() . ')',
                    'data' => null
                ], 403);
            }

            $format = strtolower($request->query('format', 'csv'));

            // Map the format query param to Excel format class extension
            $extensions = [
                'csv' => \Maatwebsite\Excel\Excel::CSV,
                'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
                'pdf' => \Maatwebsite\Excel\Excel::DOMPDF
            ];

            if (!array_key_exists($format, $extensions)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid export format. Allowed formats: csv, xlsx, pdf.',
                    'data' => null
                ], 400);
            }

            $fileName = 'closure_' . $id . '_ideas_' . now()->format('YmdHis') . '.' . $format;
            $downloadsDir = public_path('downloads');

            if (!file_exists($downloadsDir)) {
                mkdir($downloadsDir, 0755, true);
            }

            // We use the Store facade or pass public_path to Excel::store
            // Excel::store will default to saving in storage/app, so let's store it locally and generate a URL
            // Or we can save directly to the disk 'public' but in a specific folder.
            Excel::store(new IdeaExport($id), 'downloads/' . $fileName, 'public', $extensions[$format]);

            return response()->json([
                'success' => true,
                'message' => 'Ideas exported successfully.',
                'data' => [
                    'downloadUrl' => url('downloads/' . $fileName) // From public disk: local storage/app/public/downloads maps to /downloads/ (if linked properly or through storage/) Wait... the public disk root is storage/app/public. url('storage/downloads/...') would be better, but we already have public_path('downloads'). Let's use the local file path instead.
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export ideas',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
