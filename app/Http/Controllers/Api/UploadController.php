<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

final class UploadController extends Controller
{
    /**
     * Upload a file and return its storage URL.
     *
     * Accepts:
     *   file     -- the file to upload (required)
     *   folder   -- destination folder prefix (optional, defaults to 'uploads')
     *   disk     -- storage disk: public | s3 (optional, defaults to configured default)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file'   => 'required|file|max:20480', // 20 MB max
            'folder' => 'nullable|string|max:100|alpha_dash',
            'disk'   => 'nullable|string|in:public,s3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $file   = $request->file('file');
        $folder = $request->input('folder', 'uploads');
        $disk   = $request->input('disk', config('filesystems.default', 'public'));

        $extension = $file->getClientOriginalExtension();
        $filename  = Str::uuid()->toString() . '.' . $extension;
        $path      = $folder . '/' . $filename;

        $stored = Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        if (!$stored) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed',
            ], 500);
        }

        $url = Storage::disk($disk)->url($path);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data'    => [
                'url'          => $url,
                'path'         => $path,
                'disk'         => $disk,
                'filename'     => $filename,
                'original'     => $file->getClientOriginalName(),
                'mime_type'    => $file->getMimeType(),
                'size_bytes'   => $file->getSize(),
            ],
        ], 201);
    }
}
