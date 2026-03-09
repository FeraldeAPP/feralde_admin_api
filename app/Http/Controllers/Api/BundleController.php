<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\BundleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class BundleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Bundles retrieved successfully',
            'data'    => Bundle::getAll($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $bundle = Bundle::with(['items.product', 'items.variant.pricing'])->find($id);

        if (!$bundle) {
            return response()->json(['success' => false, 'message' => 'Bundle not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Bundle retrieved successfully', 'data' => $bundle]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = Bundle::validate($request->all());
        $bundle    = Bundle::createBundle($validated);

        return response()->json(['success' => true, 'message' => 'Bundle created successfully', 'data' => $bundle], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $bundle = Bundle::find($id);

        if (!$bundle) {
            return response()->json(['success' => false, 'message' => 'Bundle not found'], 404);
        }

        $validated = Bundle::validate($request->all(), $id);
        $bundle->update($validated);

        return response()->json(['success' => true, 'message' => 'Bundle updated successfully', 'data' => $bundle->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $bundle = Bundle::find($id);

        if (!$bundle) {
            return response()->json(['success' => false, 'message' => 'Bundle not found'], 404);
        }

        $bundle->delete();

        return response()->json(['success' => true, 'message' => 'Bundle deleted successfully', 'data' => null]);
    }

    public function addItem(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $bundle = Bundle::find($id);

        if (!$bundle) {
            return response()->json(['success' => false, 'message' => 'Bundle not found'], 404);
        }

        $item = BundleItem::updateOrCreate(
            ['bundle_id' => $id, 'product_id' => $request->product_id, 'variant_id' => $request->variant_id],
            ['quantity' => $request->quantity]
        );

        return response()->json(['success' => true, 'message' => 'Bundle item added', 'data' => $item], 201);
    }

    public function removeItem(int $bundleId, int $itemId): JsonResponse
    {
        $item = BundleItem::where('bundle_id', $bundleId)->find($itemId);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Bundle item not found'], 404);
        }

        $item->delete();

        return response()->json(['success' => true, 'message' => 'Bundle item removed', 'data' => null]);
    }
}
