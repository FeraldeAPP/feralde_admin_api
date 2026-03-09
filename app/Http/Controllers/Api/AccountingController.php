<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Expense;
use App\Models\FinancialSummary;
use App\Models\LedgerEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class AccountingController extends Controller
{
    public function periods(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Accounting periods retrieved successfully',
            'data'    => AccountingPeriod::getAll($request->all()),
        ]);
    }

    public function storePeriod(Request $request): JsonResponse
    {
        $validated = AccountingPeriod::validate($request->all());
        $period    = AccountingPeriod::getOrCreate($validated['year'], $validated['month']);

        return response()->json(['success' => true, 'message' => 'Period retrieved/created successfully', 'data' => $period], 201);
    }

    public function closePeriod(Request $request, int $id): JsonResponse
    {
        $period = AccountingPeriod::find($id);

        if (!$period) {
            return response()->json(['success' => false, 'message' => 'Period not found'], 404);
        }

        if ($period->is_closed) {
            return response()->json(['success' => false, 'message' => 'Period is already closed'], 422);
        }

        $userId = (string) $request->attributes->get('auth_user')['id'];
        $period->close($userId);

        return response()->json(['success' => true, 'message' => 'Period closed successfully', 'data' => $period->fresh()]);
    }

    public function ledger(Request $request, int $periodId): JsonResponse
    {
        $entries = LedgerEntry::where('period_id', $periodId)
            ->orderByDesc('entry_date')
            ->paginate((int) ($request->per_page ?? 30));

        return response()->json([
            'success' => true,
            'message' => 'Ledger entries retrieved successfully',
            'data'    => [
                'entries' => $entries->items(),
                'pagination' => [
                    'current_page' => $entries->currentPage(),
                    'last_page'    => $entries->lastPage(),
                    'per_page'     => $entries->perPage(),
                    'total'        => $entries->total(),
                ],
            ],
        ]);
    }

    public function storeLedgerEntry(Request $request, int $periodId): JsonResponse
    {
        $data               = $request->all();
        $data['period_id']  = $periodId;
        $userId             = (string) $request->attributes->get('auth_user')['id'];
        $data['created_by'] = $userId;
        $validated          = LedgerEntry::validate($data);
        $entry              = LedgerEntry::create($validated);

        return response()->json(['success' => true, 'message' => 'Ledger entry created successfully', 'data' => $entry], 201);
    }

    public function financialSummary(int $periodId): JsonResponse
    {
        $summaries = FinancialSummary::where('period_id', $periodId)->get();

        return response()->json([
            'success' => true,
            'message' => 'Financial summary retrieved successfully',
            'data'    => $summaries,
        ]);
    }

    public function expenses(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Expenses retrieved successfully',
            'data'    => Expense::getAll($request->all()),
        ]);
    }

    public function storeExpense(Request $request): JsonResponse
    {
        $userId    = (string) $request->attributes->get('auth_user')['id'];
        $validated = Expense::validate($request->all());
        $expense   = Expense::createExpense($validated, $userId);

        return response()->json(['success' => true, 'message' => 'Expense created successfully', 'data' => $expense], 201);
    }

    public function updateExpense(Request $request, int $id): JsonResponse
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found'], 404);
        }

        $validated = Expense::validate($request->all());
        $expense->update($validated);

        return response()->json(['success' => true, 'message' => 'Expense updated successfully', 'data' => $expense->fresh()]);
    }

    public function destroyExpense(int $id): JsonResponse
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Expense not found'], 404);
        }

        $expense->delete();

        return response()->json(['success' => true, 'message' => 'Expense deleted successfully', 'data' => null]);
    }

    /**
     * Export accounting data as a JSON dataset for client-side download.
     *
     * Query params:
     *   type      -- required: ledger | expenses | financial-summary
     *   period_id -- required for ledger and financial-summary
     */
    public function export(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'      => 'required|string|in:ledger,expenses,financial-summary',
            'period_id' => 'required_if:type,ledger|required_if:type,financial-summary|nullable|integer|exists:accounting_periods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $type     = $request->type;
        $periodId = $request->period_id ? (int) $request->period_id : null;

        $rows = match ($type) {
            'ledger' => LedgerEntry::where('period_id', $periodId)
                ->orderBy('entry_date')
                ->get()
                ->toArray(),

            'financial-summary' => FinancialSummary::where('period_id', $periodId)
                ->get()
                ->toArray(),

            'expenses' => Expense::when($periodId, fn ($q) => $q->where('period_id', $periodId))
                ->orderBy('expense_date')
                ->get()
                ->toArray(),
        };

        return response()->json([
            'success' => true,
            'message' => 'Export data retrieved successfully',
            'data'    => [
                'type'      => $type,
                'period_id' => $periodId,
                'count'     => count($rows),
                'rows'      => $rows,
            ],
        ]);
    }
}
