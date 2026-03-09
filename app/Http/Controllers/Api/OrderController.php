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
        $order = Order::with(['items.variant.product', 'payments', 'fulfillment', 'returns', 'invoices', 'shippingAddress', 'billingAddress', 'commissions'])->find($id);

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

        $userId = (string) $request->attributes->get('auth_user')['id'];

        if ($request->internal_notes) {
            $order->update(['internal_notes' => $request->internal_notes]);
        }

        if (!$order->transitionStatus($request->status, $userId)) {
            return response()->json(['success' => false, 'message' => 'Invalid status transition'], 422);
        }

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

    public function markPaymentPaid(Request $request, int $orderId, int $paymentId): JsonResponse
    {
        $payment = Payment::where('order_id', $orderId)->find($paymentId);

        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        $payment->markPaid($request->gateway_reference);

        return response()->json(['success' => true, 'message' => 'Payment marked as paid', 'data' => $payment->fresh()]);
    }
}
