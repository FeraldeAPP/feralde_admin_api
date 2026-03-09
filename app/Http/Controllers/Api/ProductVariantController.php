<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class ProductVariantController extends Controller
{
    public function index(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Variants retrieved successfully',
            'data'    => ProductVariant::with(['pricing', 'inventory'])->where('product_id', $productId)->get(),
        ]);
    }

    public function store(Request $request, int $productId): JsonResponse
    {
        $data              = $request->all();
        $data['product_id'] = $productId;
        $validated         = ProductVariant::validate($data);
        $variant           = ProductVariant::createVariant($validated);

        return response()->json(['success' => true, 'message' => 'Variant created successfully', 'data' => $variant->load(['pricing', 'inventory'])], 201);
    }

    public function update(Request $request, int $productId, int $variantId): JsonResponse
    {
        $data              = $request->all();
        $data['product_id'] = $productId;
        $validated         = ProductVariant::validate($data, $variantId);
        $variant           = ProductVariant::updateVariant($variantId, $validated);

        if (!$variant) {
            return response()->json(['success' => false, 'message' => 'Variant not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Variant updated successfully', 'data' => $variant]);
    }

    public function destroy(int $productId, int $variantId): JsonResponse
    {
        if (!ProductVariant::deleteVariant($variantId)) {
            return response()->json(['success' => false, 'message' => 'Variant not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Variant deleted successfully', 'data' => null]);
    }

    public function upsertPricing(Request $request, int $productId, int $variantId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tier'             => 'required|string|in:RETAIL,DISTRIBUTOR,RESELLER,WHOLESALE',
            'price'            => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'min_quantity'     => 'integer|min:1',
            'is_active'        => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $pricing = VariantPricing::upsertPricing($variantId, $request->tier, $validator->validated());

        return response()->json(['success' => true, 'message' => 'Pricing updated successfully', 'data' => $pricing]);
    }
}
