<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RatingController;
use App\Http\Controllers\Admin\StaffPayrollController;
use App\Http\Controllers\Admin\MyEarningsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Authentication
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login']);

// Protected Admin Routes — base (all admins)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/sales-overview', [DashboardController::class, 'salesOverview'])->name('admin.sales-overview');

    // Profile Settings & Change Requests (All admins / staff)
    Route::get('/profile', [DashboardController::class, 'profile'])->name('admin.profile');
    Route::post('/profile', [DashboardController::class, 'profileUpdate'])->name('admin.profile.update');

    // My Earnings (All admins / staff)
    Route::get('/my-earnings', [MyEarningsController::class, 'index'])->name('admin.my-earnings');
    Route::get('/my-earnings/print', [MyEarningsController::class, 'print'])->name('admin.my-earnings.print');

    // Dashboard Charts (all admins)
    Route::get('/dashboard/charts/orders', [DashboardController::class, 'chartOrders'])->name('admin.dashboard.charts.orders');
    Route::get('/dashboard/charts/revenue', [DashboardController::class, 'chartRevenue'])->name('admin.dashboard.charts.revenue');
    Route::get('/dashboard/charts/categories', [DashboardController::class, 'chartCategories'])->name('admin.dashboard.charts.categories');

    // Generic Trash/Soft Delete Management Routes
    Route::get('/trash/{model}', [DashboardController::class, 'viewTrash'])->name('admin.trash.index');
    Route::delete('/trash/{model}/{id}', [DashboardController::class, 'deleteModel'])->name('admin.trash.delete');
    Route::post('/trash/{model}/{id}/restore', [DashboardController::class, 'restoreModel'])->name('admin.trash.restore');
    Route::delete('/trash/{model}/{id}/force', [DashboardController::class, 'forceDeleteModel'])->name('admin.trash.force');

    // ── Operations: sellers, listings, orders, disputes, users ──────────────
    Route::middleware('admin.role:super_admin,operations')->group(function () {

        // Sellers
        Route::post('/sellers/bulk-action', [DashboardController::class, 'bulkActionSellers'])->name('admin.sellers.bulk-action');
        Route::get('/sellers', [DashboardController::class, 'sellers'])->name('admin.sellers');
        Route::get('/sellers/{id}', [DashboardController::class, 'sellerShow'])->name('admin.sellers.show');
        Route::post('/sellers/{id}/kyc', [DashboardController::class, 'sellerKyc'])->name('admin.sellers.kyc');
        Route::post('/sellers/{id}/visit-verify', [DashboardController::class, 'sellerVisitVerify'])->name('admin.sellers.visit-verify');
        Route::post('/sellers/{id}/status', [DashboardController::class, 'sellerStatusUpdate'])->name('admin.sellers.status-update');
        Route::post('/sellers/{id}/update-razorpay-account', [DashboardController::class, 'updateRazorpayAccount'])->name('admin.sellers.update-razorpay-account');
        Route::post('/sellers/{id}/badge', [DashboardController::class, 'sellerBadgeUpdate'])->name('admin.sellers.badge-update');
        Route::get('/sellers/{id}/chat', [DashboardController::class, 'getChatMessages'])->name('admin.sellers.chat.index');
        Route::post('/sellers/{id}/chat', [DashboardController::class, 'storeChatMessage'])->name('admin.sellers.chat.store');

        // Orders (write actions — operations only)
        Route::post('/orders/bulk-action', [DashboardController::class, 'bulkActionOrders'])->name('admin.orders.bulk-action');
        Route::post('/orders/{id}/status', [DashboardController::class, 'orderUpdateStatus'])->name('admin.orders.status');
        Route::post('/orders/{id}/invoice/resend', [DashboardController::class, 'resendInvoice'])->name('admin.orders.invoice.resend');
        Route::post('/orders/{id}/chat', [DashboardController::class, 'storeBuyerChatMessage'])->name('admin.orders.chat.store');

        // Disputes
        Route::post('/disputes/bulk-action', [DashboardController::class, 'bulkActionDisputes'])->name('admin.disputes.bulk-action');
        Route::get('/disputes', [DashboardController::class, 'disputes'])->name('admin.disputes');
        Route::get('/disputes/{id}', [DashboardController::class, 'disputeShow'])->name('admin.disputes.show');
        Route::post('/disputes/{id}/response', [DashboardController::class, 'disputeResponse'])->name('admin.disputes.response');
        Route::post('/disputes/{id}/resolve', [DashboardController::class, 'disputeResolve'])->name('admin.disputes.resolve');
        Route::post('/disputes/{id}/reopen', [DashboardController::class, 'disputeReopen'])->name('admin.disputes.reopen');

        // Users
        Route::get('/users', [DashboardController::class, 'users'])->name('admin.users');
        Route::post('/users/{id}/suspend', [DashboardController::class, 'userSuspend'])->name('admin.users.suspend');
        Route::post('/users/{id}/ban', [DashboardController::class, 'userBan'])->name('admin.users.ban');
    });

    // ── Orders READ — operations + support ──────────────────────────────────
    Route::middleware('admin.role:super_admin,operations,support')->group(function () {
        Route::get('/orders', [DashboardController::class, 'orders'])->name('admin.orders');
        Route::get('/orders/{id}', [DashboardController::class, 'orderShow'])->name('admin.orders.show');
        Route::get('/orders/{id}/invoice/download', [DashboardController::class, 'downloadInvoice'])->name('admin.orders.invoice.download');
        Route::get('/orders/{id}/chat', [DashboardController::class, 'getBuyerChatMessages'])->name('admin.orders.chat.index');
    });

    // ── Listings — operations + moderator ───────────────────────────────────
    Route::middleware('admin.role:super_admin,operations,moderator')->group(function () {
        Route::post('/listings/bulk-action', [DashboardController::class, 'bulkActionListings'])->name('admin.listings.bulk-action');
        Route::get('/listings', [DashboardController::class, 'listings'])->name('admin.listings');
        Route::get('/listings/{id}', [DashboardController::class, 'listingShow'])->name('admin.listings.show');
        Route::post('/listings/{id}/approve', [DashboardController::class, 'listingApprove'])->name('admin.listings.approve');
        Route::post('/listings/{id}/pause', [DashboardController::class, 'listingPause'])->name('admin.listings.pause');
    });

    // ── Support Tickets — operations + support ──────────────────────────────
    Route::middleware('admin.role:super_admin,operations,support')->group(function () {
        Route::post('/tickets/bulk-action', [DashboardController::class, 'bulkActionTickets'])->name('admin.tickets.bulk-action');
        Route::get('/tickets', [DashboardController::class, 'tickets'])->name('admin.tickets');
        Route::post('/tickets/{id}/status', [DashboardController::class, 'updateTicketStatus'])->name('admin.tickets.status');
    });

    // ── Finance: escrow + payouts + analytics ───────────────────────────────
    Route::middleware('admin.role:super_admin,finance')->group(function () {
        Route::post('/escrow/bulk-action', [DashboardController::class, 'bulkActionEscrows'])->name('admin.escrow.bulk-action');
        Route::get('/escrow', [DashboardController::class, 'escrow'])->name('admin.escrow');
        Route::post('/escrow/{id}/release', [DashboardController::class, 'escrowRelease'])->name('admin.escrow.release');
        Route::post('/escrow/{id}/refund', [DashboardController::class, 'escrowRefund'])->name('admin.escrow.refund');
        Route::post('/escrow/{id}/retry-payout', [DashboardController::class, 'escrowRetryPayout'])->name('admin.escrow.retry-payout');
        Route::post('/escrow/{id}/partial-release', [DashboardController::class, 'escrowPartialRelease'])->name('admin.escrow.partial-release');

        Route::post('/payouts/bulk-action', [DashboardController::class, 'bulkActionPayouts'])->name('admin.payouts.bulk-action');
        Route::get('/payouts/export', [DashboardController::class, 'payoutsExport'])->name('admin.payouts.export');
        Route::get('/payouts', [DashboardController::class, 'payouts'])->name('admin.payouts');

        Route::get('/analytics', [DashboardController::class, 'analyticsPage'])->name('admin.analytics');
        Route::get('/analytics/gmv', [DashboardController::class, 'analyticsGmv'])->name('admin.analytics.gmv');
        Route::get('/analytics/top-sellers', [DashboardController::class, 'analyticsTopSellers'])->name('admin.analytics.top-sellers');
        Route::get('/analytics/top-categories', [DashboardController::class, 'analyticsTopCategories'])->name('admin.analytics.top-categories');
        Route::get('/analytics/dispute-rate', [DashboardController::class, 'analyticsDisputeRate'])->name('admin.analytics.dispute-rate');
    });

    // ── Moderator: fraud flags + ratings + alerts ────────────────────────────
    Route::middleware('admin.role:super_admin,moderator')->group(function () {
        Route::get('/fraud-flags', [DashboardController::class, 'fraudFlagsIndex'])->name('admin.fraud-flags.index');
        Route::post('/fraud-flags', [DashboardController::class, 'createFraudFlagManual'])->name('admin.fraud-flags.store');
        Route::post('/fraud-flags/{id}/review', [DashboardController::class, 'reviewFraudFlagWeb'])->name('admin.fraud-flags.review');
        Route::post('/fraud-flags/{id}/dismiss', [DashboardController::class, 'dismissFraudFlagWeb'])->name('admin.fraud-flags.dismiss');

        Route::get('/ratings', [\App\Http\Controllers\Admin\RatingController::class, 'index'])->name('admin.ratings');
        Route::get('/ratings/create', [\App\Http\Controllers\Admin\RatingController::class, 'create'])->name('admin.ratings.create');
        Route::post('/ratings', [\App\Http\Controllers\Admin\RatingController::class, 'store'])->name('admin.ratings.store');
        Route::get('/ratings/{id}/edit', [\App\Http\Controllers\Admin\RatingController::class, 'edit'])->name('admin.ratings.edit');
        Route::put('/ratings/{id}', [\App\Http\Controllers\Admin\RatingController::class, 'update'])->name('admin.ratings.update');
        Route::delete('/ratings/{id}', [\App\Http\Controllers\Admin\RatingController::class, 'destroy'])->name('admin.ratings.destroy');

        Route::get('/alerts', [DashboardController::class, 'alerts'])->name('admin.alerts');
        Route::post('/alerts/{id}/read', [DashboardController::class, 'markAlertRead'])->name('admin.alerts.read');
        Route::post('/alerts/read-all', [DashboardController::class, 'markAllAlertsRead'])->name('admin.alerts.read-all');
        Route::post('/alerts/clear-all', [DashboardController::class, 'clearAllAlerts'])->name('admin.alerts.clear-all');
    });

    // ── Content: articles, pages, categories, mail ──────────────────────────
    Route::middleware('admin.role:super_admin,content')->group(function () {
        Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories');
        Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
        Route::put('/categories/reorder', [\App\Http\Controllers\Admin\CategoryController::class, 'reorder'])->name('admin.categories.reorder');
        Route::put('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        Route::put('/categories/{id}/toggle-active', [\App\Http\Controllers\Admin\CategoryController::class, 'toggleActive'])->name('admin.categories.toggle-active');

        Route::get('/spec-templates', [\App\Http\Controllers\Admin\SpecTemplateController::class, 'index'])->name('admin.spec-templates');
        Route::post('/spec-templates', [\App\Http\Controllers\Admin\SpecTemplateController::class, 'store'])->name('admin.spec-templates.store');
        Route::put('/spec-templates/{id}', [\App\Http\Controllers\Admin\SpecTemplateController::class, 'update'])->name('admin.spec-templates.update');
        Route::delete('/spec-templates/{id}', [\App\Http\Controllers\Admin\SpecTemplateController::class, 'destroy'])->name('admin.spec-templates.destroy');

        Route::get('/pages', [\App\Http\Controllers\Admin\PageController::class, 'index'])->name('admin.pages.index');
        Route::post('/pages', [\App\Http\Controllers\Admin\PageController::class, 'store'])->name('admin.pages.store');
        Route::put('/pages/reorder', [\App\Http\Controllers\Admin\PageController::class, 'reorderPages'])->name('admin.pages.reorder');
        Route::put('/page-categories/reorder', [\App\Http\Controllers\Admin\PageController::class, 'reorderCategories'])->name('admin.pages.categories.reorder');
        
        Route::get('/pages/{id}/edit', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->name('admin.pages.edit');
        Route::put('/pages/{id}', [\App\Http\Controllers\Admin\PageController::class, 'update'])->name('admin.pages.update');
        Route::delete('/pages/{id}', [\App\Http\Controllers\Admin\PageController::class, 'destroy'])->name('admin.pages.destroy');
        Route::post('/page-categories', [\App\Http\Controllers\Admin\PageController::class, 'storeCategory'])->name('admin.pages.categories.store');
        Route::delete('/page-categories/{id}', [\App\Http\Controllers\Admin\PageController::class, 'destroyCategory'])->name('admin.pages.categories.destroy');

        Route::post('/page-categories/{id}/restore', [\App\Http\Controllers\Admin\PageController::class, 'restoreCategory'])->name('admin.pages.categories.restore');
        Route::post('/pages/{id}/restore', [\App\Http\Controllers\Admin\PageController::class, 'restorePage'])->name('admin.pages.restore');
        Route::delete('/page-categories/{id}/force', [\App\Http\Controllers\Admin\PageController::class, 'forceDeleteCategory'])->name('admin.pages.categories.force-delete');
        Route::delete('/pages/{id}/force', [\App\Http\Controllers\Admin\PageController::class, 'forceDeletePage'])->name('admin.pages.force-delete');

        Route::get('/asset-library/images', [\App\Http\Controllers\Admin\AssetLibraryController::class, 'getImages'])->name('admin.asset-library.images');
        Route::get('/content', [\App\Http\Controllers\Admin\ContentManagerController::class, 'index'])->name('admin.content');
        Route::post('/content/upload', [\App\Http\Controllers\Admin\ContentManagerController::class, 'upload'])->name('admin.content.upload');
        Route::delete('/content/delete', [\App\Http\Controllers\Admin\ContentManagerController::class, 'delete'])->name('admin.content.delete');
        Route::post('/content/restore', [\App\Http\Controllers\Admin\ContentManagerController::class, 'restore'])->name('admin.content.restore');
        Route::delete('/content/force-delete', [\App\Http\Controllers\Admin\ContentManagerController::class, 'forceDelete'])->name('admin.content.force-delete');

        Route::get('/articles', [\App\Http\Controllers\Admin\ArticleController::class, 'index'])->name('admin.articles.index');
        Route::get('/articles/create', [\App\Http\Controllers\Admin\ArticleController::class, 'create'])->name('admin.articles.create');
        Route::post('/articles', [\App\Http\Controllers\Admin\ArticleController::class, 'store'])->name('admin.articles.store');
        Route::get('/articles/{id}/edit', [\App\Http\Controllers\Admin\ArticleController::class, 'edit'])->name('admin.articles.edit');
        Route::put('/articles/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'update'])->name('admin.articles.update');
        Route::delete('/articles/{id}', [\App\Http\Controllers\Admin\ArticleController::class, 'destroy'])->name('admin.articles.destroy');
        Route::post('/articles/{id}/toggle-status', [\App\Http\Controllers\Admin\ArticleController::class, 'toggleStatus'])->name('admin.articles.toggle-status');
        Route::post('/articles/bulk-action', [\App\Http\Controllers\Admin\ArticleController::class, 'bulkAction'])->name('admin.articles.bulk-action');

        Route::get('/mails', [\App\Http\Controllers\Admin\MailManagementController::class, 'index'])->name('admin.mails.index');
        Route::post('/mails/send', [\App\Http\Controllers\Admin\MailManagementController::class, 'sendMail'])->name('admin.mails.send');
        Route::post('/mails/subscribers', [\App\Http\Controllers\Admin\MailManagementController::class, 'storeSubscriber'])->name('admin.mails.subscribers.store');
        Route::delete('/mails/subscribers/{id}', [\App\Http\Controllers\Admin\MailManagementController::class, 'destroySubscriber'])->name('admin.mails.subscribers.destroy');
        Route::post('/mails/subscribers/bulk-action', [\App\Http\Controllers\Admin\MailManagementController::class, 'bulkActionSubscribers'])->name('admin.mails.subscribers.bulk-action');
        Route::get('/mails/users/search', [\App\Http\Controllers\Admin\MailManagementController::class, 'searchUsers'])->name('admin.mails.users.search');
        Route::post('/mails/users/import', [\App\Http\Controllers\Admin\MailManagementController::class, 'importUsers'])->name('admin.mails.users.import');

        Route::get('/mail/logs', [\App\Http\Controllers\Admin\MailLogController::class, 'index'])->name('admin.mail.logs');
        Route::get('/mail/logs/{id}', [\App\Http\Controllers\Admin\MailLogController::class, 'show'])->name('admin.mail.logs.show');
        Route::post('/mail/resend/{id}', [\App\Http\Controllers\Admin\MailLogController::class, 'resend'])->name('admin.mail.logs.resend');
        Route::get('/mail/templates', [\App\Http\Controllers\Admin\MailLogController::class, 'templates'])->name('admin.mail.templates');
    });

    // ── Super Admin only ─────────────────────────────────────────────────────
    Route::middleware('admin.role:super_admin')->group(function () {
        // Staff Directory & Management
        Route::get('/staff', [StaffPayrollController::class, 'staffDirectory'])->name('admin.staff.directory');
        Route::get('/staff/{id}', [StaffPayrollController::class, 'staffProfile'])->name('admin.staff.show');
        Route::post('/staff/{id}/suspend', [StaffPayrollController::class, 'suspendStaff'])->name('admin.staff.suspend');
        Route::post('/staff/{id}/terminate', [StaffPayrollController::class, 'terminateStaff'])->name('admin.staff.terminate');
        Route::post('/staff/{id}/reactivate', [StaffPayrollController::class, 'reactivateStaff'])->name('admin.staff.reactivate');
        Route::get('/staff/{id}/notice', [StaffPayrollController::class, 'sendNoticeForm'])->name('admin.staff.notice');
        Route::post('/staff/{id}/notice', [StaffPayrollController::class, 'sendNotice'])->name('admin.staff.notice.send');

        // Payroll Overview & Disburse
        Route::get('/payroll', [StaffPayrollController::class, 'payrollOverview'])->name('admin.payroll');
        Route::post('/payroll/disburse', [StaffPayrollController::class, 'disburseAll'])->name('admin.payroll.disburse');
        Route::get('/payroll/settings', [StaffPayrollController::class, 'payrollSettings'])->name('admin.payroll.settings');
        Route::post('/payroll/settings', [StaffPayrollController::class, 'savePayrollSettings'])->name('admin.payroll.settings.save');

        Route::get('/settings', [DashboardController::class, 'settings'])->name('admin.settings');
        Route::post('/settings', [DashboardController::class, 'settingsUpdate'])->name('admin.settings.update');
        Route::put('/settings/smtp', [DashboardController::class, 'settingsSmtpUpdate'])->name('admin.settings.smtp.update');
        Route::post('/settings/smtp/test', [DashboardController::class, 'testSmtpConnection'])->name('admin.settings.smtp.test');

        Route::get('/audit-logs', [DashboardController::class, 'auditLogs'])->name('admin.audit-logs');

        // Admin Account Management
        Route::get('/admin-accounts/check-email', [\App\Http\Controllers\Admin\AdminAccountController::class, 'checkEmail'])->name('admin.accounts.check-email');
        Route::get('/admin-accounts', [\App\Http\Controllers\Admin\AdminAccountController::class, 'index'])->name('admin.accounts.index');
        Route::get('/admin-accounts/create', [\App\Http\Controllers\Admin\AdminAccountController::class, 'create'])->name('admin.accounts.create');
        Route::post('/admin-accounts', [\App\Http\Controllers\Admin\AdminAccountController::class, 'store'])->name('admin.accounts.store');
        Route::get('/admin-accounts/{id}/edit', [\App\Http\Controllers\Admin\AdminAccountController::class, 'edit'])->name('admin.accounts.edit');
        Route::put('/admin-accounts/{id}', [\App\Http\Controllers\Admin\AdminAccountController::class, 'update'])->name('admin.accounts.update');
        Route::delete('/admin-accounts/{id}', [\App\Http\Controllers\Admin\AdminAccountController::class, 'destroy'])->name('admin.accounts.destroy');
    });
});

// Public Policy Pages Web View Route
Route::get('/v1/pages/{slug}', function (string $slug) {
    $page = \App\Models\Page::where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

    $allPages = \App\Models\Page::where('is_active', true)
        ->select(['title', 'slug'])
        ->get();

    return view('pages.show', compact('page', 'allPages'));
});

// Public Blog Routes
Route::get('/blog', [\App\Http\Controllers\BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [\App\Http\Controllers\BlogController::class, 'show'])->name('blog.show');

// Public User Profile Route
Route::get('/member/{id}', [\App\Http\Controllers\UserDashboardController::class, 'publicProfile'])->name('member.profile');

// Public Subscription routes
Route::post('/subscribers/subscribe', [\App\Http\Controllers\SubscriberPublicController::class, 'subscribe'])->name('subscribers.subscribe');
Route::get('/subscribers/unsubscribe/{token}', [\App\Http\Controllers\SubscriberPublicController::class, 'unsubscribe'])->name('subscribers.unsubscribe');

// User Authentication Web Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\UserAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\UserAuthController::class, 'login']);

    Route::get('/register', [\App\Http\Controllers\UserAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [\App\Http\Controllers\UserAuthController::class, 'register']);

    Route::get('/forgot-password', [\App\Http\Controllers\UserAuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password/send', [\App\Http\Controllers\UserAuthController::class, 'sendForgotPasswordOtp'])->name('password.email');
    Route::get('/forgot-password/verify', [\App\Http\Controllers\UserAuthController::class, 'showResetPassword'])->name('password.verify');
    Route::post('/forgot-password/reset', [\App\Http\Controllers\UserAuthController::class, 'resetPassword'])->name('password.update');

    // Social login redirects and callbacks
    Route::get('/auth/social/{provider}', [\App\Http\Controllers\UserAuthController::class, 'socialRedirect'])->name('social.redirect');
    Route::get('/auth/social/{provider}/callback', [\App\Http\Controllers\UserAuthController::class, 'socialCallback'])->name('social.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\UserAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [\App\Http\Controllers\UserDashboardController::class, 'profile'])->name('dashboard');
    Route::get('/dashboard/settings', [\App\Http\Controllers\UserDashboardController::class, 'settings'])->name('dashboard.settings');
    Route::post('/dashboard/settings', [\App\Http\Controllers\UserDashboardController::class, 'updateSettings']);
    Route::get('/dashboard/settings/account', [\App\Http\Controllers\UserDashboardController::class, 'accountSettings'])->name('dashboard.settings.account');
    Route::post('/dashboard/settings/account', [\App\Http\Controllers\UserDashboardController::class, 'updateAccountSettings']);
    Route::post('/dashboard/settings/account/social/{provider}/toggle', [\App\Http\Controllers\UserDashboardController::class, 'toggleSocialLink'])->name('dashboard.settings.account.social.toggle');
    Route::post('/dashboard/settings/verify-phone', [\App\Http\Controllers\UserDashboardController::class, 'verifyPhone']);
    Route::get('/dashboard/settings/shipping', [\App\Http\Controllers\UserDashboardController::class, 'shippingSettings'])->name('dashboard.settings.shipping');
    Route::post('/dashboard/settings/shipping', [\App\Http\Controllers\UserDashboardController::class, 'updateShippingSettings']);
    Route::post('/dashboard/settings/shipping-options', [\App\Http\Controllers\UserDashboardController::class, 'updateShippingOptions']);
    Route::get('/dashboard/settings/payments', [\App\Http\Controllers\UserDashboardController::class, 'paymentSettings'])->name('dashboard.settings.payments');
    Route::post('/dashboard/settings/payments', [\App\Http\Controllers\UserDashboardController::class, 'updatePaymentSettings']);
    Route::post('/dashboard/settings/payments/add-card', [\App\Http\Controllers\UserDashboardController::class, 'addCard'])->name('dashboard.settings.payments.add-card');
    Route::delete('/dashboard/settings/payments/delete-card/{index}', [\App\Http\Controllers\UserDashboardController::class, 'deleteCard'])->name('dashboard.settings.payments.delete-card');
    Route::get('/dashboard/settings/payments/bank-account', [\App\Http\Controllers\UserDashboardController::class, 'bankAccountSettings'])->name('dashboard.settings.payments.bank');
    Route::post('/dashboard/settings/payments/bank-account', [\App\Http\Controllers\UserDashboardController::class, 'updateBankAccountSettings']);
    Route::post('/dashboard/settings/payments/payouts', [\App\Http\Controllers\UserDashboardController::class, 'updatePayoutOptions'])->name('dashboard.settings.payments.payouts');
    Route::get('/dashboard/settings/notifications', [\App\Http\Controllers\UserDashboardController::class, 'notificationSettings'])->name('dashboard.settings.notifications');
    Route::post('/dashboard/settings/notifications', [\App\Http\Controllers\UserDashboardController::class, 'updateNotificationSettings']);
    Route::get('/dashboard/settings/privacy', [\App\Http\Controllers\UserDashboardController::class, 'privacySettings'])->name('dashboard.settings.privacy');
    Route::post('/dashboard/settings/privacy', [\App\Http\Controllers\UserDashboardController::class, 'updatePrivacySettings']);
    Route::get('/dashboard/settings/privacy/download-data', [\App\Http\Controllers\UserDashboardController::class, 'manageAccountData'])->name('dashboard.settings.privacy.download-data');
    Route::post('/dashboard/settings/privacy/download-data/export', [\App\Http\Controllers\UserDashboardController::class, 'downloadAccountData'])->name('dashboard.settings.privacy.download-data.export');
    Route::get('/dashboard/settings/security', [\App\Http\Controllers\UserDashboardController::class, 'securitySettings'])->name('dashboard.settings.security');
    Route::get('/dashboard/settings/email/confirm', [\App\Http\Controllers\UserDashboardController::class, 'confirmEmailChange'])->name('dashboard.settings.email.confirm');
    Route::post('/dashboard/settings/email/send-confirmation', [\App\Http\Controllers\UserDashboardController::class, 'sendEmailConfirmation'])->name('dashboard.settings.email.send-confirmation');
    Route::get('/dashboard/settings/email/verify', [\App\Http\Controllers\UserDashboardController::class, 'showVerifyEmailOtp'])->name('dashboard.settings.email.verify');
    Route::post('/dashboard/settings/email/verify', [\App\Http\Controllers\UserDashboardController::class, 'verifyEmailOtp']);
    Route::get('/dashboard/settings/email/update', [\App\Http\Controllers\UserDashboardController::class, 'showUpdateEmailForm'])->name('dashboard.settings.email.update');
    Route::post('/dashboard/settings/email/update', [\App\Http\Controllers\UserDashboardController::class, 'updateEmail']);
    Route::get('/dashboard/settings/security/two-step', [\App\Http\Controllers\UserDashboardController::class, 'showTwoStepVerification'])->name('dashboard.settings.security.two-step');
    Route::post('/dashboard/settings/security/two-step/verify', [\App\Http\Controllers\UserDashboardController::class, 'verifyTwoStepOtp']);
    Route::get('/dashboard/settings/security/sessions', [\App\Http\Controllers\UserDashboardController::class, 'showSessionsActivity'])->name('dashboard.settings.security.sessions');
    Route::get('/dashboard/settings/change-password', [\App\Http\Controllers\UserDashboardController::class, 'showChangePassword'])->name('dashboard.settings.change-password.show');
    Route::post('/dashboard/settings/change-password', [\App\Http\Controllers\UserDashboardController::class, 'changePassword'])->name('dashboard.settings.change-password');
    Route::get('/dashboard/settings/delete-account', [\App\Http\Controllers\UserDashboardController::class, 'showDeleteAccount'])->name('dashboard.settings.delete-account.show');
    Route::post('/dashboard/settings/delete-account', [\App\Http\Controllers\UserDashboardController::class, 'deleteAccount'])->name('dashboard.settings.delete-account');
    Route::get('/dashboard/wallet', [\App\Http\Controllers\UserDashboardController::class, 'showWallet'])->name('dashboard.wallet');
    Route::get('/dashboard/wallet/setup', [\App\Http\Controllers\UserDashboardController::class, 'showWalletSetup'])->name('dashboard.wallet.setup');
    Route::post('/dashboard/wallet/setup', [\App\Http\Controllers\UserDashboardController::class, 'saveWalletSetup'])->name('dashboard.wallet.setup.save');
    Route::get('/dashboard/wallet/history', [\App\Http\Controllers\UserDashboardController::class, 'showWalletHistory'])->name('dashboard.wallet.history');
    Route::get('/dashboard/wallet/invoices', [\App\Http\Controllers\UserDashboardController::class, 'showWalletInvoices'])->name('dashboard.wallet.invoices');
    Route::get('/dashboard/wallet/invoices/{id}/download', [\App\Http\Controllers\UserDashboardController::class, 'downloadUserInvoice'])->name('dashboard.wallet.invoices.download');
    Route::get('/dashboard/wallet/invoices/{id}/view', [\App\Http\Controllers\UserDashboardController::class, 'viewUserInvoice'])->name('dashboard.wallet.invoices.view');
    Route::get('/dashboard/wallet/income', [\App\Http\Controllers\UserDashboardController::class, 'showWalletIncome'])->name('dashboard.wallet.income');
    Route::get('/dashboard/wallet/income/download/{year}/{month}', [\App\Http\Controllers\UserDashboardController::class, 'downloadMonthlyIncomeReport'])->name('dashboard.wallet.income.download');
    Route::post('/dashboard/wallet/funds', [\App\Http\Controllers\UserDashboardController::class, 'manageWalletFunds'])->name('dashboard.wallet.funds');
    Route::get('/dashboard/orders', [\App\Http\Controllers\UserDashboardController::class, 'showOrders'])->name('dashboard.orders');
});

Route::get('/listings', function (\Illuminate\Http\Request $request) {
    $query = \App\Models\Listing::active()->with(['images', 'seller']);
    
    if ($request->has('search') && !empty($request->input('search'))) {
        $search = $request->input('search');
        $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%");
        });
    }

    if ($request->has('category') && !empty($request->input('category'))) {
        $query->where('category', $request->input('category'));
    }

    $listings = $query->latest()->paginate(12);
    return view('listings.index', compact('listings'));
})->name('listings.index');

Route::get('/listings/{slug}', function (string $slug) {
    $listing = \App\Models\Listing::with(['images', 'seller.user', 'specs'])->where('slug', $slug)->firstOrFail();
    return view('listings.show', compact('listing'));
})->name('listings.show');

Route::get('/run-seeds', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);
    return "Database migrated and seeded successfully!";
});






