<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PromoCodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Promo codes retrieved successfully',
            'data'    => PromoCode::getAll($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Promo code not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Promo code retrieved successfully', 'data' => $promo]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = PromoCode::validate($request->all());
        $promo     = PromoCode::createPromo($validated);

        return response()->json(['success' => true, 'message' => 'Promo code created successfully', 'data' => $promo], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Promo code not found'], 404);
        }

        $validated = PromoCode::validate($request->all(), $id);
        $promo->update($validated);

        return response()->json(['success' => true, 'message' => 'Promo code updated successfully', 'data' => $promo->fresh()]);
    }

    public function destroy(int $id): JsonResponse
    {
        $promo = PromoCode::find($id);

        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Promo code not found'], 404);
        }

        $promo->delete();

        return response()->json(['success' => true, 'message' => 'Promo code deleted successfully', 'data' => null]);
    }
}
