<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fulfillment;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully',
            'data'    => Order::getAll($request->all()),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with(['items.variant.product.media', 'payments', 'fulfillment', 'returns', 'invoices', 'shippingAddress', 'billingAddress', 'commissions'])->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Order retrieved successfully', 'data' => $order]);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:PENDING,CONFIRMED,PROCESSING,PACKED,SHIPPED,OUT_FOR_DELIVERY,DELIVERED,CANCELLED',
            'internal_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $user = $request->attributes->get('auth_user');
        $userId = $user['id'] ?? null;
        $userName = $user['name'] ?? null;

        if ($request->internal_notes) {
            $order->update(['internal_notes' => $request->internal_notes]);
        }

        if (!$order->transitionStatus($request->status, (string)$userId)) {
            return response()->json(['success' => false, 'message' => 'Invalid status transition'], 422);
        }

        $order->logHistory(
            'STATUS_UPDATED',
            "Order status changed to {$request->status}",
            (string)$userId,
            (string)$userName,
            ['new_status' => $request->status]
        );

        return response()->json(['success' => true, 'message' => 'Order status updated', 'data' => $order->fresh()]);
    }

    public function storeFulfillment(Request $request, int $id): JsonResponse
    {
        $data               = $request->all();
        $data['order_id']   = $id;
        $validated          = Fulfillment::validate($data);
        $fulfillment        = Fulfillment::createForOrder($validated);

        return response()->json(['success' => true, 'message' => 'Fulfillment created successfully', 'data' => $fulfillment], 201);
    }

    public function storeReturn(Request $request, int $id): JsonResponse
    {
        $data              = $request->all();
        $data['order_id']  = $id;
        $validated         = OrderReturn::validate($data);
        $return            = OrderReturn::create($validated);

        return response()->json(['success' => true, 'message' => 'Return created successfully', 'data' => $return], 201);
    }

    public function storeInvoice(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'subtotal' => 'required|numeric|min:0',
            'tax'      => 'required|numeric|min:0',
            'total'    => 'required|numeric|min:0',
            'due_at'   => 'nullable|date',
            'notes'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $invoice = Invoice::createForOrder($id, $validator->validated());

        return response()->json(['success' => true, 'message' => 'Invoice created successfully', 'data' => $invoice], 201);
    }

    public function markPaymentPaid(Request $request, int $id): JsonResponse
    {
        $order = Order::with('payments')->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Find the first non-paid payment record
        $payment = $order->payments()->where('status', '!=', 'PAID')->first();

        if (!$payment) {
            // Create a manual payment record if none exists
            $payment = Payment::create([
                'order_id' => $order->id,
                'method'   => $order->payment_method ?? 'MANUAL',
                'status'   => 'PENDING',
                'amount'   => $order->total_amount,
                'currency' => 'PHP',
            ]);
        }

        $payment->markPaid($request->gateway_reference);

        $user = $request->attributes->get('auth_user');
        $userId = $user['id'] ?? null;
        $userName = $user['name'] ?? null;

        $order->logHistory(
            'PAYMENT_CONFIRMED',
            "Payment of PHP " . number_format((float)$payment->amount, 2) . " confirmed via {$payment->method}",
            (string)$userId,
            (string)$userName,
            ['payment_id' => $payment->id, 'amount' => $payment->amount, 'method' => $payment->method]
        );

        return response()->json([
            'success' => true,
            'message' => 'Order marked as paid',
            'data'    => $order->fresh(['payments', 'items'])
        ]);
    }

    public function history(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $history = $order->histories()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Order history retrieved successfully',
            'data'    => $history
        ]);
    }
}
