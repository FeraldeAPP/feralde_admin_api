<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResellerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ResellerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Resellers retrieved successfully',
            'data'    => ResellerProfile::getAll($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $reseller = ResellerProfile::with(['parentDistributor', 'wallet', 'commissions'])->find($id);

        if (!$reseller) {
            return response()->json(['success' => false, 'message' => 'Reseller not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Reseller retrieved successfully', 'data' => $reseller]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $reseller = ResellerProfile::find($id);

        if (!$reseller) {
            return response()->json(['success' => false, 'message' => 'Reseller not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $reseller->approve($userId);

        return response()->json(['success' => true, 'message' => 'Reseller approved', 'data' => $reseller->fresh()]);
    }
}
