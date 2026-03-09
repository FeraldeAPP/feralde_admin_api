<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

final class SalesChannel extends Model
{
    protected $fillable = [
        'type',
        'name',
        'external_store_id',
        'credentials',
        'webhook_secret',
        'is_active',
        'last_synced_at',
        'sync_settings',
    ];

    protected $casts = [
        'credentials'    => 'array',
        'sync_settings'  => 'array',
        'is_active'      => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function channelProducts(): HasMany
    {
        return $this->hasMany(ChannelProduct::class, 'channel_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'channel_id');
    }

    public static function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'type'              => 'required|string|in:WEBSITE,TIKTOK_SHOP,LAZADA,SHOPEE,MANUAL|unique:sales_channels,type',
            'name'              => 'required|string|max:255',
            'external_store_id' => 'nullable|string|max:255',
            'credentials'       => 'nullable|array',
            'webhook_secret'    => 'nullable|string',
            'is_active'         => 'boolean',
            'sync_settings'     => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422));
        }

        return $validator->validated();
    }

    /**
     * Push all active channel products to the external platform.
     * Dispatches to the correct platform handler based on channel type.
     *
     * @return array{synced: int, failed: int, errors: array<int, string>}
     */
    public function syncProducts(): array
    {
        return match ($this->type) {
            'TIKTOK_SHOP' => $this->syncToTikTok(),
            'LAZADA'      => $this->syncToLazada(),
            default       => ['synced' => 0, 'failed' => 0, 'errors' => ['Sync not supported for channel type: ' . $this->type]],
        };
    }

    /**
     * Pull recent orders from the external platform.
     *
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    public function fetchExternalOrders(array $params = []): array
    {
        return match ($this->type) {
            'TIKTOK_SHOP' => $this->fetchTikTokOrders($params),
            'LAZADA'      => $this->fetchLazadaOrders($params),
            default       => [],
        };
    }

    /**
     * Verify an inbound webhook signature for this channel.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (!$this->webhook_secret) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $this->webhook_secret);
        return hash_equals($expected, $signature);
    }

    // --- TikTok Shop integration ---

    /**
     * @return array{synced: int, failed: int, errors: array<int, string>}
     */
    private function syncToTikTok(): array
    {
        $credentials  = $this->credentials ?? [];
        $appKey       = (string) ($credentials['app_key'] ?? '');
        $accessToken  = (string) ($credentials['access_token'] ?? '');
        $shopId       = (string) ($this->external_store_id ?? '');

        $synced = 0;
        $failed = 0;
        $errors = [];

        $products = $this->channelProducts()->with('variant.product')->get();

        foreach ($products as $channelProduct) {
            try {
                $timestamp = time();
                $path      = '/product/202309/products/' . $channelProduct->external_listing_id;
                $sign      = $this->tikTokSign($appKey, $path, $timestamp, (string) ($credentials['app_secret'] ?? ''));

                $response = Http::withHeaders([
                    'x-tts-access-token' => $accessToken,
                    'content-type'       => 'application/json',
                ])->put("https://open-api.tiktokglobalshop.com{$path}?app_key={$appKey}&timestamp={$timestamp}&sign={$sign}&shop_id={$shopId}", [
                    'skus' => [
                        [
                            'id'         => $channelProduct->external_sku,
                            'stock_infos' => [
                                ['available_stock' => (int) ($channelProduct->external_stock ?? 0)],
                            ],
                            'price' => [
                                'currency'      => 'PHP',
                                'original_price' => (string) ($channelProduct->external_price ?? 0),
                            ],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $channelProduct->update(['last_synced_at' => now()]);
                    $synced++;
                } else {
                    $failed++;
                    $errors[] = 'TikTok sync failed for listing ' . $channelProduct->external_listing_id . ': ' . $response->status();
                }
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = 'TikTok sync exception: ' . $e->getMessage();
                Log::error('TikTok product sync failed', ['error' => $e->getMessage(), 'channel_id' => $this->id]);
            }
        }

        $this->update(['last_synced_at' => now()]);

        return compact('synced', 'failed', 'errors');
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    private function fetchTikTokOrders(array $params): array
    {
        $credentials = $this->credentials ?? [];
        $appKey      = (string) ($credentials['app_key'] ?? '');
        $accessToken = (string) ($credentials['access_token'] ?? '');
        $shopId      = (string) ($this->external_store_id ?? '');
        $timestamp   = time();
        $path        = '/order/202309/orders/search';
        $sign        = $this->tikTokSign($appKey, $path, $timestamp, (string) ($credentials['app_secret'] ?? ''));

        try {
            $response = Http::withHeaders([
                'x-tts-access-token' => $accessToken,
                'content-type'       => 'application/json',
            ])->post("https://open-api.tiktokglobalshop.com{$path}?app_key={$appKey}&timestamp={$timestamp}&sign={$sign}&shop_id={$shopId}", [
                'order_status' => $params['order_status'] ?? 'AWAITING_SHIPMENT',
                'page_size'    => $params['page_size'] ?? 20,
            ]);

            return $response->json('data.orders') ?? [];
        } catch (\Throwable $e) {
            Log::error('TikTok order fetch failed', ['error' => $e->getMessage(), 'channel_id' => $this->id]);
            return [];
        }
    }

    private function tikTokSign(string $appKey, string $path, int $timestamp, string $appSecret): string
    {
        $input = $appSecret . $path . 'app_key' . $appKey . 'timestamp' . $timestamp . $appSecret;
        return hash_hmac('sha256', $input, $appSecret);
    }

    // --- Lazada integration ---

    /**
     * @return array{synced: int, failed: int, errors: array<int, string>}
     */
    private function syncToLazada(): array
    {
        $credentials = $this->credentials ?? [];
        $appKey      = (string) ($credentials['app_key'] ?? '');
        $appSecret   = (string) ($credentials['app_secret'] ?? '');
        $accessToken = (string) ($credentials['access_token'] ?? '');

        $synced = 0;
        $failed = 0;
        $errors = [];

        $products = $this->channelProducts()->with('variant.product')->get();

        foreach ($products as $channelProduct) {
            try {
                $timestamp  = (string) (time() * 1000);
                $params     = [
                    'app_key'      => $appKey,
                    'timestamp'    => $timestamp,
                    'access_token' => $accessToken,
                    'sign_method'  => 'sha256',
                ];
                $params['sign'] = $this->lazadaSign('/products/price_quantity/update', $params, $appSecret);

                $response = Http::withQueryParameters($params)
                    ->post('https://api.lazada.com.ph/rest/products/price_quantity/update', [
                        'payload' => json_encode([
                            'Request' => [
                                'Product' => [
                                    'Skus' => [
                                        'Sku' => [
                                            'SkuId'         => $channelProduct->external_sku,
                                            'SalePrice'     => (string) ($channelProduct->external_price ?? 0),
                                            'Quantity'      => (string) ($channelProduct->external_stock ?? 0),
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                    ]);

                if ($response->successful() && ($response->json('code') === '0' || $response->json('code') === 0)) {
                    $channelProduct->update(['last_synced_at' => now()]);
                    $synced++;
                } else {
                    $failed++;
                    $errors[] = 'Lazada sync failed for SKU ' . $channelProduct->external_sku . ': ' . $response->json('message');
                }
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = 'Lazada sync exception: ' . $e->getMessage();
                Log::error('Lazada product sync failed', ['error' => $e->getMessage(), 'channel_id' => $this->id]);
            }
        }

        $this->update(['last_synced_at' => now()]);

        return compact('synced', 'failed', 'errors');
    }

    /**
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    private function fetchLazadaOrders(array $params): array
    {
        $credentials = $this->credentials ?? [];
        $appKey      = (string) ($credentials['app_key'] ?? '');
        $appSecret   = (string) ($credentials['app_secret'] ?? '');
        $accessToken = (string) ($credentials['access_token'] ?? '');
        $timestamp   = (string) (time() * 1000);

        $callParams = [
            'app_key'      => $appKey,
            'timestamp'    => $timestamp,
            'access_token' => $accessToken,
            'sign_method'  => 'sha256',
            'status'       => $params['status'] ?? 'pending',
        ];
        $callParams['sign'] = $this->lazadaSign('/orders/get', $callParams, $appSecret);

        try {
            $response = Http::get('https://api.lazada.com.ph/rest/orders/get', $callParams);
            return $response->json('data.orders') ?? [];
        } catch (\Throwable $e) {
            Log::error('Lazada order fetch failed', ['error' => $e->getMessage(), 'channel_id' => $this->id]);
            return [];
        }
    }

    /**
     * @param array<string, string> $params
     */
    private function lazadaSign(string $apiPath, array $params, string $appSecret): string
    {
        ksort($params);
        $concatStr = $apiPath;
        foreach ($params as $k => $v) {
            $concatStr .= $k . $v;
        }
        return strtoupper(hash_hmac('sha256', $concatStr, $appSecret));
    }
}
