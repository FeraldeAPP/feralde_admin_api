<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Warehouses retrieved successfully',
            'data'    => Warehouse::withCount('inventory')->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $warehouse = Warehouse::withCount('inventory')->find($id);

        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Warehouse not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Warehouse retrieved successfully', 'data' => $warehouse]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = Warehouse::validate($request->all());
        $warehouse = Warehouse::createWarehouse($validated);

        return response()->json(['success' => true, 'message' => 'Warehouse created successfully', 'data' => $warehouse], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = Warehouse::validate($request->all(), $id);
        $warehouse = Warehouse::updateWarehouse($id, $validated);

        if (!$warehouse) {
            return response()->json(['success' => false, 'message' => 'Warehouse not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Warehouse updated successfully', 'data' => $warehouse]);
    }
}
