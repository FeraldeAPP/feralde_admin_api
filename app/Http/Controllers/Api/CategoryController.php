<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data'    => Category::getAll($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::with(['children', 'parent'])->find($id);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Category retrieved successfully', 'data' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = Category::validate($request->all());
        $category  = Category::createCategory($validated);

        return response()->json(['success' => true, 'message' => 'Category created successfully', 'data' => $category], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = Category::validate($request->all(), $id);
        $category  = Category::updateCategory($id, $validated);

        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Category updated successfully', 'data' => $category]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (!Category::deleteCategory($id)) {
            return response()->json(['success' => false, 'message' => 'Category not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Category deleted successfully', 'data' => null]);
    }
}
