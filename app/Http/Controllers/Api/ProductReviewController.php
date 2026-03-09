<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Reviews retrieved successfully',
            'data'    => ProductReview::getAll($request->all()),
        ]);
    }

    public function approve(int $id): JsonResponse
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review not found'], 404);
        }

        $review->approve();

        return response()->json(['success' => true, 'message' => 'Review approved', 'data' => $review->fresh()]);
    }

    public function reject(int $id): JsonResponse
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review not found'], 404);
        }

        $review->reject();

        return response()->json(['success' => true, 'message' => 'Review rejected', 'data' => $review->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $review = ProductReview::find($id);

        if (!$review) {
            return response()->json(['success' => false, 'message' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json(['success' => true, 'message' => 'Review deleted successfully', 'data' => null]);
    }
}
