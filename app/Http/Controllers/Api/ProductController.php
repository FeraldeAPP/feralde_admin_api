<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data'    => Product::getProducts($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::getProductById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data'    => $product,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = Product::validateStore($request->all());
        $product   = Product::createProduct($validated);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data'    => $product,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = Product::validateUpdate($request->all(), $id);
        $product   = Product::updateProductById($id, $validated);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data'    => $product,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!Product::deleteProductById($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
            'data'    => null,
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        if (!Product::restoreProductById($id)) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product restored successfully',
            'data'    => null,
        ]);
    }
}
