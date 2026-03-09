<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelProduct;
use App\Models\SalesChannel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class SalesChannelController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Sales channels retrieved successfully',
            'data'    => SalesChannel::withCount('channelProducts')->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $channel = SalesChannel::withCount(['channelProducts', 'orders'])->find($id);

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Sales channel not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Sales channel retrieved successfully', 'data' => $channel]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = SalesChannel::validate($request->all());
        $channel   = SalesChannel::create($validated);

        return response()->json(['success' => true, 'message' => 'Sales channel created successfully', 'data' => $channel], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $channel = SalesChannel::find($id);

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Sales channel not found'], 404);
        }

        $channel->update($request->only(['name', 'is_active', 'sync_settings', 'webhook_secret', 'credentials']));

        return response()->json(['success' => true, 'message' => 'Sales channel updated successfully', 'data' => $channel->fresh()]);
    }

    public function channelProducts(Request $request, int $channelId): JsonResponse
    {
        $products = ChannelProduct::with(['variant.product'])
            ->where('channel_id', $channelId)
            ->paginate((int) ($request->per_page ?? 20));

        return response()->json([
            'success' => true,
            'message' => 'Channel products retrieved successfully',
            'data'    => [
                'products'   => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                ],
            ],
        ]);
    }

    public function syncChannelProduct(Request $request, int $channelId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'variant_id'          => 'required|integer|exists:product_variants,id',
            'external_listing_id' => 'required|string|max:255',
            'external_sku'        => 'nullable|string|max:100',
            'external_price'      => 'nullable|numeric|min:0',
            'external_stock'      => 'nullable|integer|min:0',
            'is_active'           => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $product = ChannelProduct::updateOrCreate(
            ['channel_id' => $channelId, 'variant_id' => $request->variant_id],
            array_merge($validator->validated(), ['last_synced_at' => now()])
        );

        return response()->json(['success' => true, 'message' => 'Channel product synced', 'data' => $product]);
    }

    /**
     * Trigger a full product sync push to the external channel (TikTok/Lazada).
     */
    public function sync(int $id): JsonResponse
    {
        $channel = SalesChannel::find($id);

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Sales channel not found'], 404);
        }

        if (!$channel->is_active) {
            return response()->json(['success' => false, 'message' => 'Channel is inactive'], 422);
        }

        $result = $channel->syncProducts();

        return response()->json([
            'success' => true,
            'message' => 'Sync completed',
            'data'    => $result,
        ]);
    }

    /**
     * Fetch orders from the external channel (TikTok/Lazada).
     */
    public function externalOrders(Request $request, int $id): JsonResponse
    {
        $channel = SalesChannel::find($id);

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Sales channel not found'], 404);
        }

        $orders = $channel->fetchExternalOrders($request->only(['status', 'order_status', 'page_size']));

        return response()->json([
            'success' => true,
            'message' => 'External orders retrieved',
            'data'    => ['orders' => $orders, 'count' => count($orders)],
        ]);
    }

    /**
     * Handle inbound webhooks from external channels.
     * Endpoint is public but verified by HMAC signature per channel config.
     */
    public function webhook(Request $request, int $id): JsonResponse
    {
        $channel = SalesChannel::find($id);

        if (!$channel) {
            return response()->json(['success' => false, 'message' => 'Channel not found'], 404);
        }

        $rawBody  = $request->getContent();
        $sigHeader = match ($channel->type) {
            'TIKTOK_SHOP' => $request->header('Webhook-Signature') ?? '',
            'LAZADA'      => $request->header('Authorization') ?? '',
            default       => $request->header('X-Webhook-Signature') ?? '',
        };

        if (!$channel->verifyWebhookSignature($rawBody, $sigHeader)) {
            Log::warning('Channel webhook: invalid signature', ['channel_id' => $id, 'type' => $channel->type]);
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $event   = (string) ($payload['type'] ?? $payload['event'] ?? 'unknown');

        Log::info('Channel webhook received', ['channel_id' => $id, 'type' => $channel->type, 'event' => $event]);

        return response()->json(['success' => true, 'message' => 'Webhook received']);
    }
}
