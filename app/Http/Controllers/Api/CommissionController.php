<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\CommissionRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CommissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Commissions retrieved successfully',
            'data'    => Commission::getAll($request->all()),
        ]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $commission = Commission::find($id);

        if (!$commission) {
            return response()->json(['success' => false, 'message' => 'Commission not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];

        if (!$commission->approve($userId)) {
            return response()->json(['success' => false, 'message' => 'Commission cannot be approved'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Commission approved', 'data' => $commission->fresh()]);
    }

    public function pay(int $id): JsonResponse
    {
        $commission = Commission::find($id);

        if (!$commission) {
            return response()->json(['success' => false, 'message' => 'Commission not found'], 404);
        }

        if (!$commission->pay()) {
            return response()->json(['success' => false, 'message' => 'Commission cannot be paid'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Commission paid and wallet credited', 'data' => $commission->fresh()]);
    }

    // Commission Rules

    public function rules(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Commission rules retrieved successfully',
            'data'    => CommissionRule::getAll($request->all()),
        ]);
    }

    public function storeRule(Request $request): JsonResponse
    {
        $validated = CommissionRule::validate($request->all());
        $rule      = CommissionRule::createRule($validated);

        return response()->json(['success' => true, 'message' => 'Commission rule created successfully', 'data' => $rule], 201);
    }

    public function updateRule(Request $request, int $ruleId): JsonResponse
    {
        $rule = CommissionRule::find($ruleId);

        if (!$rule) {
            return response()->json(['success' => false, 'message' => 'Commission rule not found'], 404);
        }

        $validated = CommissionRule::validate($request->all());
        $rule->update($validated);

        return response()->json(['success' => true, 'message' => 'Commission rule updated successfully', 'data' => $rule->fresh()]);
    }
}
