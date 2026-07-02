<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\SellerController;
use App\Http\Controllers\Api\WebhookController;

// Public Auth routes
Route::middleware('throttle:auth')->group(function () {
    Route::post('/auth/otp/send', [AuthController::class, 'sendOtp']);
    Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
});

// Public Listings routes
Route::get('/listings', [ListingController::class, 'index']);
Route::get('/listings/{slug}', [ListingController::class, 'show']);
Route::get('/v1/sitemap/listings', [ListingController::class, 'sitemap']);
Route::get('/v1/sitemap/pages', [\App\Http\Controllers\Api\PageController::class, 'sitemap']);

// Public policy pages routes
Route::get('/v1/pages', [\App\Http\Controllers\Api\PageController::class, 'index']);
Route::get('/v1/pages/{slug}', [\App\Http\Controllers\Api\PageController::class, 'show']);

// API Categories routes
Route::prefix('v1')->group(function () {
    Route::get('/categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [\App\Http\Controllers\Api\CategoryController::class, 'show']);
    Route::get('/categories/{slug}/listings', [\App\Http\Controllers\Api\CategoryController::class, 'listings']);
    Route::get('/brands', [\App\Http\Controllers\Api\BrandController::class, 'index']);
});

// Webhook routes
Route::post('/webhooks/razorpay', [WebhookController::class, 'razorpay']);
Route::post('/webhooks/shiprocket', [WebhookController::class, 'shiprocket']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Buyer routes
    Route::prefix('buyer')->group(function () {
        Route::get('/orders', [BuyerController::class, 'getOrders']);
        Route::get('/orders/{id}', [BuyerController::class, 'getOrder']);
        Route::post('/orders', [BuyerController::class, 'createOrder']);
        Route::post('/orders/{id}/pay', [BuyerController::class, 'payOrder']);
        Route::post('/orders/{id}/confirm-delivery', [BuyerController::class, 'confirmDelivery']);
        Route::post('/orders/{id}/dispute', [BuyerController::class, 'raiseDispute']);
        Route::post('/orders/{id}/rate', [BuyerController::class, 'rateSeller']);
        Route::get('/wishlist', [BuyerController::class, 'getWishlist']);
        Route::post('/wishlist/{listing_id}', [BuyerController::class, 'addToWishlist']);
        Route::delete('/wishlist/{listing_id}', [BuyerController::class, 'removeFromWishlist']);
        Route::post('/wishlist/toggle', [BuyerController::class, 'toggleWishlist']);
        Route::post('/tickets', [BuyerController::class, 'createTicket']);
        Route::post('/checkout', [BuyerController::class, 'checkout']);
    });

    // Seller routes
    Route::prefix('seller')->group(function () {
        Route::get('/dashboard', [SellerController::class, 'dashboard']);
        Route::get('/listings', [SellerController::class, 'getListings']);
        Route::post('/listings', [SellerController::class, 'createListing']);
        Route::put('/listings/{id}', [SellerController::class, 'updateListing']);
        Route::delete('/listings/{id}', [SellerController::class, 'deleteListing']);
        Route::get('/orders', [SellerController::class, 'getOrders']);
        Route::get('/orders/{id}', [SellerController::class, 'getOrder']);
        Route::post('/orders/{id}/confirm', [SellerController::class, 'confirmOrder']);
        Route::get('/disputes', [SellerController::class, 'getDisputes']);
        Route::post('/disputes/{id}/respond', [SellerController::class, 'respondToDispute']);
        Route::get('/sold-archive', [SellerController::class, 'getSoldArchive']);
    });

    // Support Ticket routes
    Route::prefix('v1/support/tickets')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\SupportTicketController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\SupportTicketController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\SupportTicketController::class, 'show']);
        Route::post('/{id}/reply', [\App\Http\Controllers\Api\SupportTicketController::class, 'reply']);
    });

    // Admin Support Ticket routes
    Route::prefix('admin/support/tickets')->group(function () {
        Route::put('/{id}/assign', [\App\Http\Controllers\Api\SupportTicketController::class, 'assign']);
        Route::put('/{id}/close', [\App\Http\Controllers\Api\SupportTicketController::class, 'close']);
    });

    // Admin Article routes
    Route::prefix('admin/articles')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ArticleApiController::class, 'adminIndex']);
        Route::post('/', [\App\Http\Controllers\Api\ArticleApiController::class, 'adminStore']);
        Route::put('/{id}', [\App\Http\Controllers\Api\ArticleApiController::class, 'adminUpdate']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\ArticleApiController::class, 'adminDestroy']);
    });

    // Wallet routes
    Route::prefix('v1/wallet')->group(function () {
        Route::get('/balance', [\App\Http\Controllers\Api\WalletController::class, 'balance']);
        Route::get('/transactions', [\App\Http\Controllers\Api\WalletController::class, 'transactions']);
    });

    // Admin Wallet routes
    Route::prefix('admin/wallet')->group(function () {
        Route::put('/credit', [\App\Http\Controllers\Admin\DashboardController::class, 'creditWallet']);
    });

    // Admin Fraud Flags routes
    Route::prefix('admin/fraud-flags')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'listFraudFlags']);
        Route::put('/{id}/review', [\App\Http\Controllers\Admin\DashboardController::class, 'reviewFraudFlag']);
        Route::put('/{id}/dismiss', [\App\Http\Controllers\Admin\DashboardController::class, 'dismissFraudFlag']);
    });
});

// Public Articles & Ratings API routes
Route::prefix('v1')->group(function () {
    Route::get('/articles', [\App\Http\Controllers\Api\ArticleApiController::class, 'index']);
    Route::get('/articles/{slug}', [\App\Http\Controllers\Api\ArticleApiController::class, 'show']);
    Route::get('/sellers/{shop_slug}/ratings', [\App\Http\Controllers\Api\ListingController::class, 'sellerRatingsByShop']);
    Route::get('/listings/{slug}/seller-ratings', [\App\Http\Controllers\Api\ListingController::class, 'sellerRatingsByListing']);
});


