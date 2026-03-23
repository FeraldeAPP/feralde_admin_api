<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\BundleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\DistributorController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\MarketingController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductMediaController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ProductVariantController;
use App\Http\Controllers\Api\PromoCodeController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ResellerController;
use App\Http\Controllers\Api\SalesChannelController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\WarehouseController;

/*
|--------------------------------------------------------------------------
| API Routes -- feralde_ecom_admin_api (Admin Panel)
|--------------------------------------------------------------------------
|
| All routes return JSON only. Authentication is delegated to feralde_auth
| via the auth.proxy middleware which validates the session cookie.
|
| All routes require a valid session and at least one permission check.
|
*/

// Channel webhooks -- public, verified by HMAC signature inside the controller
Route::middleware(['force.json'])->group(function (): void {
    Route::post('/sales-channels/{id}/webhook', [SalesChannelController::class, 'webhook']);
});

// Public registration endpoints -- no authentication required
// These allow prospective resellers to register via a distributor's shareable link
Route::middleware(['force.json'])->prefix('register')->group(function (): void {
    Route::get('/distributor/{referralCode}', [RegistrationController::class, 'distributorProfile']);
    Route::post('/reseller/{referralCode}', [RegistrationController::class, 'registerReseller']);
});

Route::middleware(['force.json', 'auth.proxy'])->group(function (): void {

    // Health check
    Route::get('/health', fn () => response()->json(['status' => 'ok']));

    // File upload
    Route::post('/upload', [UploadController::class, 'store'])->middleware('permission:system.manage');

    // Products
    Route::prefix('products')->group(function (): void {
        Route::get('/', [ProductController::class, 'index'])->middleware('permission:products.view');
        Route::post('/', [ProductController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [ProductController::class, 'show'])->middleware('permission:products.view');
        Route::put('/{id}', [ProductController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('permission:products.delete');
        Route::post('/{id}/restore', [ProductController::class, 'restore'])->middleware('permission:products.delete');

        // Variants (nested under product)
        Route::prefix('/{productId}/variants')->group(function (): void {
            Route::get('/', [ProductVariantController::class, 'index'])->middleware('permission:products.view');
            Route::post('/', [ProductVariantController::class, 'store'])->middleware('permission:products.update');
            Route::put('/{variantId}', [ProductVariantController::class, 'update'])->middleware('permission:products.update');
            Route::delete('/{variantId}', [ProductVariantController::class, 'destroy'])->middleware('permission:products.update');
            Route::put('/{variantId}/pricing', [ProductVariantController::class, 'upsertPricing'])->middleware('permission:products.update');
        });

        // Media (nested under product)
        Route::prefix('/{productId}/media')->group(function (): void {
            Route::post('/', [ProductMediaController::class, 'store'])->middleware('permission:products.update');
            Route::delete('/{mediaId}', [ProductMediaController::class, 'destroy'])->middleware('permission:products.update');
        });
    });

    // Categories
    Route::prefix('categories')->group(function (): void {
        Route::get('/', [CategoryController::class, 'index'])->middleware('permission:categories.view');
        Route::post('/', [CategoryController::class, 'store'])->middleware('permission:categories.create');
        Route::get('/{id}', [CategoryController::class, 'show'])->middleware('permission:categories.view');
        Route::put('/{id}', [CategoryController::class, 'update'])->middleware('permission:categories.update');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete');
    });

    // Product reviews
    Route::prefix('reviews')->group(function (): void {
        Route::get('/', [ProductReviewController::class, 'index'])->middleware('permission:reviews.view');
        Route::post('/{id}/approve', [ProductReviewController::class, 'approve'])->middleware('permission:reviews.moderate');
        Route::post('/{id}/reject', [ProductReviewController::class, 'reject'])->middleware('permission:reviews.moderate');
        Route::delete('/{id}', [ProductReviewController::class, 'destroy'])->middleware('permission:reviews.moderate');
    });

    // Inventory
    Route::prefix('inventory')->group(function (): void {
        Route::get('/', [InventoryController::class, 'index'])->middleware('permission:inventory.view');
        Route::post('/adjust', [InventoryController::class, 'adjust'])->middleware('permission:inventory.manage');
        Route::get('/alerts', [InventoryController::class, 'alerts'])->middleware('permission:inventory.view');
        Route::post('/alerts/{id}/resolve', [InventoryController::class, 'resolveAlert'])->middleware('permission:inventory.manage');
        Route::get('/movements', [InventoryController::class, 'movements'])->middleware('permission:inventory.view');
    });

    // Warehouses
    Route::prefix('warehouses')->group(function (): void {
        Route::get('/', [WarehouseController::class, 'index'])->middleware('permission:inventory.view');
        Route::post('/', [WarehouseController::class, 'store'])->middleware('permission:inventory.manage');
        Route::get('/{id}', [WarehouseController::class, 'show'])->middleware('permission:inventory.view');
        Route::put('/{id}', [WarehouseController::class, 'update'])->middleware('permission:inventory.manage');
    });

    // Orders
    Route::prefix('orders')->group(function (): void {
        Route::get('/', [OrderController::class, 'index'])->middleware('permission:orders.view');
        Route::get('/{id}', [OrderController::class, 'show'])->middleware('permission:orders.view');
        Route::post('/{id}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.manage');
        Route::post('/{id}/fulfillments', [OrderController::class, 'storeFulfillment'])->middleware('permission:orders.manage');
        Route::post('/{id}/returns', [OrderController::class, 'storeReturn'])->middleware('permission:orders.manage');
        Route::post('/{id}/invoices', [OrderController::class, 'storeInvoice'])->middleware('permission:orders.manage');
        Route::post('/{id}/payments/paid', [OrderController::class, 'markPaymentPaid'])->middleware('permission:orders.manage');
    });

    // Distributors
    Route::prefix('distributors')->group(function (): void {
        Route::get('/', [DistributorController::class, 'index'])->middleware('permission:distributors.view');
        Route::get('/pending', [DistributorController::class, 'pending'])->middleware('permission:distributors.view');
        Route::get('/city', [DistributorController::class, 'cityDistributor'])->middleware('permission:distributors.view');
        Route::post('/', [DistributorController::class, 'store'])->middleware('permission:distributors.manage');
        Route::get('/{id}', [DistributorController::class, 'show'])->middleware('permission:distributors.view');
        Route::put('/{id}', [DistributorController::class, 'update'])->middleware('permission:distributors.manage');
        Route::post('/{id}/approve', [DistributorController::class, 'approve'])->middleware('permission:distributors.manage');
        Route::post('/{id}/reject', [DistributorController::class, 'reject'])->middleware('permission:distributors.manage');
        Route::post('/{id}/suspend', [DistributorController::class, 'suspend'])->middleware('permission:distributors.manage');
        Route::post('/{id}/unsuspend', [DistributorController::class, 'unsuspend'])->middleware('permission:distributors.manage');
        Route::post('/{id}/rank', [DistributorController::class, 'updateRank'])->middleware('permission:distributors.manage');
        Route::post('/{id}/city', [DistributorController::class, 'assignCity'])->middleware('permission:distributors.manage');
        Route::delete('/{id}/city', [DistributorController::class, 'unassignCity'])->middleware('permission:distributors.manage');
        Route::get('/{id}/network-resellers', [DistributorController::class, 'networkResellers'])->middleware('permission:distributors.view');
    });

    // Resellers
    Route::prefix('resellers')->group(function (): void {
        Route::get('/', [ResellerController::class, 'index'])->middleware('permission:resellers.view');
        Route::get('/cities', [ResellerController::class, 'cityStats'])->middleware('permission:resellers.view');
        Route::get('/{id}', [ResellerController::class, 'show'])->middleware('permission:resellers.view');
        Route::post('/{id}/approve', [ResellerController::class, 'approve'])->middleware('permission:resellers.manage');
    });

    // Replicated stores
    Route::prefix('stores')->group(function (): void {
        Route::get('/', [StoreController::class, 'index'])->middleware('permission:distributors.view');
        Route::get('/{id}', [StoreController::class, 'show'])->middleware('permission:distributors.view');
        Route::put('/{id}', [StoreController::class, 'update'])->middleware('permission:distributors.manage');
        Route::post('/{id}/toggle', [StoreController::class, 'toggle'])->middleware('permission:distributors.manage');
    });

    // Commissions
    Route::prefix('commissions')->group(function (): void {
        Route::get('/', [CommissionController::class, 'index'])->middleware('permission:commissions.view');
        Route::post('/{id}/approve', [CommissionController::class, 'approve'])->middleware('permission:commissions.manage');
        Route::post('/{id}/pay', [CommissionController::class, 'pay'])->middleware('permission:commissions.manage');

        // Commission rules
        Route::prefix('/rules')->group(function (): void {
            Route::get('/', [CommissionController::class, 'rules'])->middleware('permission:commissions.view');
            Route::post('/', [CommissionController::class, 'storeRule'])->middleware('permission:commissions.manage');
            Route::put('/{id}', [CommissionController::class, 'updateRule'])->middleware('permission:commissions.manage');
            Route::delete('/{id}', [CommissionController::class, 'destroyRule'])->middleware('permission:commissions.manage');
        });
    });

    // Wallets
    Route::prefix('wallets')->group(function (): void {
        Route::get('/', [WalletController::class, 'index'])->middleware('permission:wallets.view');
        Route::get('/{id}', [WalletController::class, 'show'])->middleware('permission:wallets.view');
        Route::get('/{id}/withdrawals', [WalletController::class, 'withdrawals'])->middleware('permission:wallets.view');
        Route::post('/withdrawals/{id}/approve', [WalletController::class, 'approveWithdrawal'])->middleware('permission:wallets.manage');
        Route::post('/withdrawals/{id}/reject', [WalletController::class, 'rejectWithdrawal'])->middleware('permission:wallets.manage');
    });

    // Bundles
    Route::prefix('bundles')->group(function (): void {
        Route::get('/', [BundleController::class, 'index'])->middleware('permission:products.view');
        Route::post('/', [BundleController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [BundleController::class, 'show'])->middleware('permission:products.view');
        Route::put('/{id}', [BundleController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [BundleController::class, 'destroy'])->middleware('permission:products.delete');
        Route::post('/{id}/items', [BundleController::class, 'addItem'])->middleware('permission:products.update');
        Route::delete('/{id}/items/{itemId}', [BundleController::class, 'removeItem'])->middleware('permission:products.update');
    });

    // Promo codes
    Route::prefix('promo-codes')->group(function (): void {
        Route::get('/', [PromoCodeController::class, 'index'])->middleware('permission:promos.view');
        Route::post('/', [PromoCodeController::class, 'store'])->middleware('permission:promos.manage');
        Route::get('/{id}', [PromoCodeController::class, 'show'])->middleware('permission:promos.view');
        Route::put('/{id}', [PromoCodeController::class, 'update'])->middleware('permission:promos.manage');
        Route::delete('/{id}', [PromoCodeController::class, 'destroy'])->middleware('permission:promos.manage');
    });

    // Training
    Route::prefix('training')->group(function (): void {
        Route::get('/', [TrainingController::class, 'index'])->middleware('permission:training.view');
        Route::post('/', [TrainingController::class, 'store'])->middleware('permission:training.manage');
        Route::get('/completions', [TrainingController::class, 'completions'])->middleware('permission:training.view');
        Route::get('/{id}', [TrainingController::class, 'show'])->middleware('permission:training.view');
        Route::put('/{id}', [TrainingController::class, 'update'])->middleware('permission:training.manage');
        Route::delete('/{id}', [TrainingController::class, 'destroy'])->middleware('permission:training.manage');
        Route::post('/{id}/complete', [TrainingController::class, 'recordCompletion'])->middleware('permission:training.manage');

        // Training contents (nested under module)
        Route::prefix('/{moduleId}/contents')->group(function (): void {
            Route::get('/', [TrainingController::class, 'indexContent'])->middleware('permission:training.view');
            Route::post('/', [TrainingController::class, 'storeContent'])->middleware('permission:training.manage');
            Route::put('/{contentId}', [TrainingController::class, 'updateContent'])->middleware('permission:training.manage');
            Route::delete('/{contentId}', [TrainingController::class, 'destroyContent'])->middleware('permission:training.manage');
        });
    });

    // Marketing (assets + announcements)
    Route::prefix('marketing')->group(function (): void {
        // Assets
        Route::prefix('/assets')->group(function (): void {
            Route::get('/', [MarketingController::class, 'assets'])->middleware('permission:marketing.view');
            Route::post('/', [MarketingController::class, 'storeAsset'])->middleware('permission:marketing.manage');
            Route::put('/{id}', [MarketingController::class, 'updateAsset'])->middleware('permission:marketing.manage');
            Route::delete('/{id}', [MarketingController::class, 'destroyAsset'])->middleware('permission:marketing.manage');
        });

        // Announcements
        Route::prefix('/announcements')->group(function (): void {
            Route::get('/', [MarketingController::class, 'announcements'])->middleware('permission:marketing.view');
            Route::post('/', [MarketingController::class, 'storeAnnouncement'])->middleware('permission:marketing.manage');
            Route::put('/{id}', [MarketingController::class, 'updateAnnouncement'])->middleware('permission:marketing.manage');
            Route::delete('/{id}', [MarketingController::class, 'destroyAnnouncement'])->middleware('permission:marketing.manage');
            Route::post('/{id}/publish', [MarketingController::class, 'publishAnnouncement'])->middleware('permission:marketing.manage');
        });
    });

    // Accounting
    Route::prefix('accounting')->group(function (): void {
        // Periods
        Route::get('/periods', [AccountingController::class, 'periods'])->middleware('permission:accounting.view');
        Route::post('/periods', [AccountingController::class, 'storePeriod'])->middleware('permission:accounting.manage');
        Route::post('/periods/{id}/close', [AccountingController::class, 'closePeriod'])->middleware('permission:accounting.manage');

        // Ledger and summaries scoped to a period
        Route::get('/periods/{periodId}/ledger', [AccountingController::class, 'ledger'])->middleware('permission:accounting.view');
        Route::post('/periods/{periodId}/ledger', [AccountingController::class, 'storeLedgerEntry'])->middleware('permission:accounting.manage');
        Route::get('/periods/{periodId}/summaries', [AccountingController::class, 'financialSummary'])->middleware('permission:accounting.view');

        // Expenses
        Route::get('/expenses', [AccountingController::class, 'expenses'])->middleware('permission:accounting.view');
        Route::post('/expenses', [AccountingController::class, 'storeExpense'])->middleware('permission:accounting.manage');
        Route::put('/expenses/{id}', [AccountingController::class, 'updateExpense'])->middleware('permission:accounting.manage');
        Route::delete('/expenses/{id}', [AccountingController::class, 'destroyExpense'])->middleware('permission:accounting.manage');

        // Export (returns JSON dataset for client-side download/processing)
        Route::get('/export', [AccountingController::class, 'export'])->middleware('permission:accounting.view');
    });

    // Sales channels
    Route::prefix('sales-channels')->group(function (): void {
        Route::get('/', [SalesChannelController::class, 'index'])->middleware('permission:channels.view');
        Route::post('/', [SalesChannelController::class, 'store'])->middleware('permission:channels.manage');
        Route::get('/{id}', [SalesChannelController::class, 'show'])->middleware('permission:channels.view');
        Route::put('/{id}', [SalesChannelController::class, 'update'])->middleware('permission:channels.manage');
        Route::get('/{id}/products', [SalesChannelController::class, 'channelProducts'])->middleware('permission:channels.view');
        Route::post('/{id}/products', [SalesChannelController::class, 'syncChannelProduct'])->middleware('permission:channels.manage');
        Route::post('/{id}/sync', [SalesChannelController::class, 'sync'])->middleware('permission:channels.manage');
        Route::get('/{id}/external-orders', [SalesChannelController::class, 'externalOrders'])->middleware('permission:channels.view');
    });

    // System (settings, audit logs, leaderboard, notifications)
    Route::prefix('system')->group(function (): void {
        Route::get('/settings', [SystemController::class, 'settings'])->middleware('permission:system.view');
        Route::put('/settings/{key}', [SystemController::class, 'updateSetting'])->middleware('permission:system.manage');
        Route::get('/audit-logs', [SystemController::class, 'auditLogs'])->middleware('permission:system.view');
        Route::get('/leaderboard', [SystemController::class, 'leaderboard'])->middleware('permission:system.view');
        Route::post('/leaderboard', [SystemController::class, 'upsertLeaderboardEntry'])->middleware('permission:system.manage');
        Route::post('/notifications', [SystemController::class, 'sendNotification'])->middleware('permission:system.manage');
        Route::get('/notifications', [SystemController::class, 'notifications'])->middleware('permission:system.view');
    });

});
