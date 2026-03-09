<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductMediaController extends Controller
{
    public function store(Request $request, int $productId): JsonResponse
    {
        $data               = $request->all();
        $data['product_id'] = $productId;
        $validated          = ProductMedia::validate($data);
        $media              = ProductMedia::addMedia($validated);

        return response()->json(['success' => true, 'message' => 'Media added successfully', 'data' => $media], 201);
    }

    public function destroy(int $productId, int $mediaId): JsonResponse
    {
        $media = ProductMedia::where('product_id', $productId)->find($mediaId);

        if (!$media) {
            return response()->json(['success' => false, 'message' => 'Media not found'], 404);
        }

        $media->delete();

        return response()->json(['success' => true, 'message' => 'Media deleted successfully', 'data' => null]);
    }
}
