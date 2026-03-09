<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReplicatedStoreSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class StoreController extends Controller
{
    public function index(): JsonResponse
    {
        $stores = ReplicatedStoreSettings::getAll();

        return response()->json([
            'success' => true,
            'message' => 'Stores retrieved successfully',
            'data'    => [
                'stores'     => $stores->items(),
                'pagination' => [
                    'current_page' => $stores->currentPage(),
                    'last_page'    => $stores->lastPage(),
                    'per_page'     => $stores->perPage(),
                    'total'        => $stores->total(),
                ],
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $store = ReplicatedStoreSettings::with(['distributor', 'reseller'])->find($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Store retrieved successfully',
            'data'    => $store,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $store = ReplicatedStoreSettings::find($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'store_name'      => 'sometimes|string|max:200',
            'store_slug'      => 'sometimes|string|max:100|unique:replicated_store_settings,store_slug,' . $id,
            'banner_url'      => 'nullable|string|max:500',
            'welcome_message' => 'nullable|string|max:1000',
            'facebook_url'    => 'nullable|string|max:300',
            'instagram_url'   => 'nullable|string|max:300',
            'tiktok_url'      => 'nullable|string|max:300',
            'is_active'       => 'sometimes|boolean',
        ], [
            'store_slug.unique' => 'This store slug is already taken',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $store->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Store updated successfully',
            'data'    => $store->fresh(['distributor', 'reseller']),
        ]);
    }

    public function toggle(int $id): JsonResponse
    {
        $store = ReplicatedStoreSettings::find($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found',
            ], 404);
        }

        $store->update(['is_active' => !$store->is_active]);

        return response()->json([
            'success' => true,
            'message' => $store->is_active ? 'Store activated' : 'Store deactivated',
            'data'    => $store->fresh(),
        ]);
    }
}
