<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class WalletController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Wallets retrieved successfully',
            'data'    => Wallet::getAll($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $wallet = Wallet::with(['distributor', 'reseller', 'transactions', 'withdrawals'])->find($id);

        if (!$wallet) {
            return response()->json(['success' => false, 'message' => 'Wallet not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Wallet retrieved successfully', 'data' => $wallet]);
    }

    public function withdrawals(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal requests retrieved successfully',
            'data'    => WithdrawalRequest::getAll($request->all()),
        ]);
    }

    public function approveWithdrawal(Request $request, int $withdrawalId): JsonResponse
    {
        $withdrawal = WithdrawalRequest::with('wallet')->find($withdrawalId);

        if (!$withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal request not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];

        if (!$withdrawal->approve($userId)) {
            return response()->json(['success' => false, 'message' => 'Cannot approve withdrawal'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Withdrawal approved', 'data' => $withdrawal->fresh()]);
    }

    public function rejectWithdrawal(Request $request, int $withdrawalId): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $withdrawal = WithdrawalRequest::with('wallet')->find($withdrawalId);

        if (!$withdrawal) {
            return response()->json(['success' => false, 'message' => 'Withdrawal request not found'], 404);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $withdrawal->reject($userId, $request->reason);

        return response()->json(['success' => true, 'message' => 'Withdrawal rejected', 'data' => $withdrawal->fresh()]);
    }
}
