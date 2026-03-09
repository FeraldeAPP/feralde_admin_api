<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\InventoryAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Inventory retrieved successfully',
            'data'    => Inventory::getAll($request->all()),
        ]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'variant_id'   => 'required|integer|exists:product_variants,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'quantity'     => 'required|integer',
            'type'         => 'required|string|in:STOCK_IN,STOCK_OUT,ADJUSTMENT,DAMAGED,RETURNED,TRANSFERRED,RESERVED,RELEASED',
            'notes'        => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $userId    = (string) $request->attributes->get('auth_user')['id'];
        $inventory = Inventory::adjustStock(
            $request->variant_id,
            $request->warehouse_id,
            $request->quantity,
            $request->type,
            $request->notes ?? '',
            $userId
        );

        return response()->json(['success' => true, 'message' => 'Stock adjusted successfully', 'data' => $inventory]);
    }

    public function alerts(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Alerts retrieved successfully',
            'data'    => InventoryAlert::getUnresolved($request->all()),
        ]);
    }

    public function resolveAlert(Request $request, int $alertId): JsonResponse
    {
        $alert = InventoryAlert::find($alertId);

        if (!$alert) {
            return response()->json(['success' => false, 'message' => 'Alert not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $alert->resolve($userId);

        return response()->json(['success' => true, 'message' => 'Alert resolved', 'data' => $alert->fresh()]);
    }

    public function movements(Request $request): JsonResponse
    {
        $movements = \App\Models\StockMovement::getAll($request->all());

        return response()->json(['success' => true, 'message' => 'Stock movements retrieved successfully', 'data' => $movements]);
    }
}
