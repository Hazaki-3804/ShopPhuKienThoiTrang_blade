<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\StatisticsController as AdminStatisticsController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromotionController as AdminPromotionController;
use App\Http\Controllers\Admin\ShippingFeeController as AdminShippingFeeController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewsController as AdminReviewsController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserOrderController;
use App\Http\Controllers\VnpayController;
use App\Http\Controllers\PayosController;
use App\Http\Controllers\SePayController;
use App\Http\Controllers\MomoController;

Route::get('/', fn() => redirect()->route('home'));

// Sitemap (public, exclude admin by design)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

//Home routes
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Shop routes
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{id}', [ShopController::class, 'show'])->name('shop.show');


// Invoice routes
Route::get('/invoice/{orderId}', [InvoiceController::class, 'show'])->name('invoice.show');

// Product reviews routes
Route::post('/reviews/{productId}', [ReviewController::class, 'store'])->name('reviews.store');

// Static pages
Route::get('/about', fn() => view('pages.about'))->name('about');
Route::get('/contact', fn() => view('pages.contact'))->name('contact');

// Chatbot
Route::post('/api/chatbot', [ChatbotController::class, 'chat'])->name('chatbot.chat');
Route::post('/api/chatbot/greet', [ChatbotController::class, 'greet'])->name('chatbot.greet');

// Payment webhooks (không cần auth)
Route::post('/sepay/callback', [SePayController::class, 'callback'])->name('sepay.callback');
Route::post('/checkout/momo/notify', [MomoController::class, 'notifyPayment'])->name('momo.notify');

// API kiểm tra trạng thái thanh toán
Route::get('/api/payment/check/{orderId}', [SePayController::class, 'checkPaymentStatus'])->name('payment.check');

// Profile routes
Route::middleware('auth')->group(function () {
    // Cart and Checkout routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/buy-now/{id}', [CartController::class, 'buyNow'])->name('cart.buynow');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/save-address', [CheckoutController::class, 'saveAddress'])->name('checkout.saveAddress');
    Route::post('/checkout/calculate-shipping-fee', [CheckoutController::class, 'calculateShippingFeeAjax'])->name('checkout.calculateShippingFee');
    Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/place', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/checkout/momo/return', [CheckoutController::class, 'momoReturn'])->name('checkout.momo.return');
    Route::post('/checkout/momo/notify', [CheckoutController::class, 'momoNotify'])->name('checkout.momo.notify');
    // VNPAY
    Route::get('/checkout/vnpay', [VnpayController::class, 'createPayment'])->name('vnpay.create');
    Route::get('/checkout/vnpay-return', [VnpayController::class, 'returnPayment'])->name('vnpay.return');
    Route::get('/checkout/vnpay-success', [VnpayController::class, 'successPayment'])->name('vnpay.success');
    Route::get('/checkout/vnpay-failed', [VnpayController::class, 'failedPayment'])->name('vnpay.failed');

    // PAYOS
    Route::post('/checkout/payos', [PayosController::class, 'handlePayOSWebhook'])->name('payos.create');
    Route::get('/checkout/payos/success', [PayosController::class, 'paymentSuccess'])->name('payos.success');
    Route::get('/checkout/payos/cancel', [PayosController::class, 'paymentCancel'])->name('payos.cancel');

    // SEPAY
    Route::get('/checkout/sepay', [SePayController::class, 'createPayment'])->name('sepay.create');
    Route::get('/checkout/sepay-return', [SePayController::class, 'returnPayment'])->name('sepay.return');

    // MOMO
    Route::get('/checkout/momo', [MomoController::class, 'createPayment'])->name('momo.create');
    Route::get('/checkout/momo/return', [MomoController::class, 'returnPayment'])->name('momo.return');

    // Invoice routes
    Route::get('/invoice/{orderId}', [InvoiceController::class, 'show'])->name('invoice.show');

    // Product reviews routes
    Route::post('/reviews/{productId}', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    // User Orders overview
    Route::get('/my-orders', [UserOrderController::class, 'index'])->name('user.orders.index');
    Route::get('/my-orders/{order}', [UserOrderController::class, 'show'])->name('user.orders.show');
    Route::patch('/my-orders/{order}/cancel', [UserOrderController::class, 'cancel'])->name('user.orders.cancel');
});


// Admin routes
Route::middleware(['auth', 'checkAdmin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [AdminDashboardController::class, 'getStatsApi'])->name('dashboard.stats');
    Route::get('/api/dashboard/charts', [AdminDashboardController::class, 'getChartsApi'])->name('dashboard.charts');   
    Route::get('/settings', fn() => view('admin.settings'))->name('settings');
    // Reviews management
    Route::get('/admin/reviews', [AdminReviewsController::class, 'index'])->middleware('permission:view reviews')->name('admin.reviews.index');
    Route::patch('/admin/reviews/{review}/toggle', [AdminReviewsController::class, 'toggleVisibility'])->middleware('permission:hide reviews')->name('admin.reviews.toggle');
    Route::delete('/admin/reviews/{review}', [AdminReviewsController::class, 'destroy'])->middleware('permission:delete reviews')->name('admin.reviews.destroy');

    // Customer management (add more routes later)
    Route::name('admin.customers.')->group(function () {
        Route::get('/admin/customers/data', [AdminCustomerController::class, 'data'])->middleware('permission:view customers')->name('data');
        Route::get('/admin/customers', [AdminCustomerController::class, 'index'])->middleware('permission:view customers')->name('index');
        Route::get('/admin/customers/{id}', [AdminCustomerController::class, 'show'])->middleware('permission:view customers')->name('show');
        Route::post('/admin/customers', [AdminCustomerController::class, 'store'])->middleware('permission:edit customers')->name('store');
        Route::put('/admin/customers/update', [AdminCustomerController::class, 'update'])->middleware('permission:edit customers')->name('update');
        Route::post('/admin/customers/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->middleware('permission:edit customers')->name('toggle-status');
        Route::delete('/admin/customers/delete', [AdminCustomerController::class, 'destroy'])->middleware('permission:delete customers')->name('destroy');
    });
    // Statistics module
    Route::name('admin.statistics.')->group(function () {
        Route::get('/admin/statistics', [AdminStatisticsController::class, 'index'])->middleware('permission:view reports')->name('index');
        
        // Customer analytics
        Route::get('/admin/statistics/customers', [AdminStatisticsController::class, 'customerAnalytics'])->middleware('permission:view reports')->name('customers');
        Route::get('/admin/statistics/customers/data', [AdminStatisticsController::class, 'customerAnalyticsData'])->middleware('permission:view reports')->name('customers.data');
        Route::get('/admin/statistics/customers/export/excel', [AdminStatisticsController::class, 'exportCustomersExcel'])->middleware('permission:view reports')->name('customers.export.excel');
        Route::get('/admin/statistics/customers/export/pdf', [AdminStatisticsController::class, 'exportCustomersPdf'])->middleware('permission:view reports')->name('customers.export.pdf');
        
        // Product analytics
        Route::get('/admin/statistics/products', [AdminStatisticsController::class, 'productAnalytics'])->middleware('permission:view reports')->name('products');
        Route::get('/admin/statistics/products/data', [AdminStatisticsController::class, 'productAnalyticsData'])->middleware('permission:view reports')->name('products.data');
        Route::get('/admin/statistics/products/chart-data', [AdminStatisticsController::class, 'productChartData'])->middleware('permission:view reports')->name('products.chart');
        Route::get('/admin/statistics/products/export/excel', [AdminStatisticsController::class, 'exportProductsExcel'])->middleware('permission:view reports')->name('products.export.excel');
        
        // Time analytics
        Route::get('/admin/statistics/time', [AdminStatisticsController::class, 'timeAnalytics'])->middleware('permission:view reports')->name('time');
        Route::get('/admin/statistics/time/data', [AdminStatisticsController::class, 'timeAnalyticsData'])->middleware('permission:view reports')->name('time.data');
    });
 // Statistics module
 Route::name('admin.statistics.')->group(function () {
    Route::get('/admin/statistics', [AdminStatisticsController::class, 'index'])->name('index');
    
    // Customer analytics
    Route::get('/admin/statistics/customers', [AdminStatisticsController::class, 'customerAnalytics'])->name('customers');
    Route::get('/admin/statistics/customers/data', [AdminStatisticsController::class, 'customerAnalyticsData'])->name('customers.data');
    Route::get('/admin/statistics/customers/export/excel', [AdminStatisticsController::class, 'exportCustomersExcel'])->name('customers.export.excel');
    Route::get('/admin/statistics/customers/export/pdf', [AdminStatisticsController::class, 'exportCustomersPdf'])->name('customers.export.pdf');
    
    // Product analytics
    Route::get('/admin/statistics/products', [AdminStatisticsController::class, 'productAnalytics'])->name('products');
    Route::get('/admin/statistics/products/data', [AdminStatisticsController::class, 'productAnalyticsData'])->name('products.data');
    Route::get('/admin/statistics/products/chart-data', [AdminStatisticsController::class, 'productChartData'])->name('products.chart');
    Route::get('/admin/statistics/products/export/excel', [AdminStatisticsController::class, 'exportProductsExcel'])->name('products.export.excel');
    
    // Time analytics
    Route::get('/admin/statistics/time', [AdminStatisticsController::class, 'timeAnalytics'])->name('time');
    Route::get('/admin/statistics/time/data', [AdminStatisticsController::class, 'timeAnalyticsData'])->name('time.data');
});
    // Category management
    Route::name('admin.categories.')->group(function () {
        Route::get('/admin/categories/data', [AdminCategoryController::class, 'data'])->middleware('permission:view categories')->name('data');
        Route::get('/admin/categories/stats', [AdminCategoryController::class, 'getStats'])->middleware('permission:view categories')->name('stats');
        Route::get('/admin/categories', [AdminCategoryController::class, 'index'])->middleware('permission:view categories')->name('index');
        Route::post('/admin/categories', [AdminCategoryController::class, 'store'])->middleware('permission:create categories')->name('store');
        Route::put('/admin/categories/update', [AdminCategoryController::class, 'update'])->middleware('permission:edit categories')->name('update');
        Route::delete('/admin/categories/delete', [AdminCategoryController::class, 'destroy'])->middleware('permission:delete categories')->name('destroy');
        Route::delete('/admin/categories/delete-multiple', [AdminCategoryController::class, 'destroyMultiple'])->middleware('permission:delete categories')->name('destroy.multiple');
    });
    // Product management
    Route::name('admin.products.')->group(function () {
        Route::get('/admin/products/data', [AdminProductController::class, 'data'])->middleware('permission:view products')->name('data');
        Route::get('/admin/products/stats', [AdminProductController::class, 'getStats'])->middleware('permission:view products')->name('stats');
        Route::get('/admin/products', [AdminProductController::class, 'index'])->middleware('permission:view products')->name('index');
        Route::get('/admin/products/create', [AdminProductController::class, 'create'])->middleware('permission:create products')->name('create');
        Route::post('/admin/products', [AdminProductController::class, 'store'])->middleware('permission:create products')->name('store');
        Route::get('/admin/products/{id}', [AdminProductController::class, 'show'])->middleware('permission:view products')->name('show');
        Route::get('/admin/products/{id}/edit', [AdminProductController::class, 'edit'])->middleware('permission:edit products')->name('edit');
        Route::put('/admin/products/update', [AdminProductController::class, 'update'])->middleware('permission:edit products')->name('update');
        Route::post('/admin/products/upload-image', [AdminProductController::class, 'uploadImage'])->middleware('permission:create products')->name('upload-image');
        Route::post('/admin/products/clear-temp-images', [AdminProductController::class, 'clearTempImages'])->middleware('permission:edit products')->name('clear-temp-images');
        Route::delete('/admin/products/delete', [AdminProductController::class, 'destroy'])->middleware('permission:delete products')->name('destroy');
        Route::delete('/admin/products/delete-multiple', [AdminProductController::class, 'destroyMultiple'])->middleware('permission:delete products')->name('destroy.multiple');
    });

    // Promotion management
    Route::name('admin.promotions.')->group(function () {
        Route::get('/admin/promotions/data', [AdminPromotionController::class, 'data'])->name('data');
        Route::get('/admin/promotions', [AdminPromotionController::class, 'index'])->name('index');
        Route::get('/admin/promotions/create', [AdminPromotionController::class, 'create'])->name('create');
        Route::post('/admin/promotions', [AdminPromotionController::class, 'store'])->name('store');
        Route::get('/admin/promotions/{id}/edit', [AdminPromotionController::class, 'edit'])->name('edit');
        Route::put('/admin/promotions/update', [AdminPromotionController::class, 'update'])->name('update');
        Route::delete('/admin/promotions/delete', [AdminPromotionController::class, 'destroy'])->name('destroy');
        Route::delete('/admin/promotions/delete-multiple', [AdminPromotionController::class, 'destroyMultiple'])->name('destroy.multiple');
        Route::get('/admin/promotions/export/excel', [AdminPromotionController::class, 'exportExcel'])->name('export.excel');
        Route::get('/admin/promotions/export/pdf', [AdminPromotionController::class, 'exportPdf'])->name('export.pdf');
    });

    // Shipping Fee management
    Route::name('admin.shipping-fees.')->group(function () {
        Route::get('/admin/shipping-fees/data', [AdminShippingFeeController::class, 'data'])->name('data');
        Route::get('/admin/shipping-fees', [AdminShippingFeeController::class, 'index'])->name('index');
        Route::post('/admin/shipping-fees', [AdminShippingFeeController::class, 'store'])->name('store');
        Route::put('/admin/shipping-fees/update', [AdminShippingFeeController::class, 'update'])->name('update');
        Route::delete('/admin/shipping-fees/delete', [AdminShippingFeeController::class, 'destroy'])->name('destroy');
        Route::delete('/admin/shipping-fees/delete-multiple', [AdminShippingFeeController::class, 'destroyMultiple'])->name('destroy.multiple');
    });

    // Order management
    Route::name('admin.orders.')->group(function () {
        Route::patch('admin/orders/{order}', [OrderController::class, 'update'])->middleware('permission:edit orders')->name('update');
        Route::get('/admin/orders/data', [OrderController::class, 'data'])->middleware('permission:view orders')->name('data');
        Route::get('/admin/orders/stats', [OrderController::class, 'getStats'])->middleware('permission:view orders')->name('stats');
        Route::get('/admin/orders', [OrderController::class, 'index'])->middleware('permission:view orders')->name('index');
        Route::get('/admin/orders/{id}/detail', [OrderController::class, 'getDetail'])->middleware('permission:view orders')->name('detail');
        Route::get('/admin/orders/{id}', [OrderController::class, 'show'])->middleware('permission:view orders')->name('show');
        Route::get('/admin/orders/{id}/print', [OrderController::class, 'print'])->middleware('permission:view orders')->name('print');
        Route::post('/admin/orders/update-status', [OrderController::class, 'updateStatus'])->middleware('permission:edit orders')->name('update-status');
        Route::delete('/admin/orders/delete', [OrderController::class, 'destroy'])->middleware('permission:delete orders')->name('destroy');
    });

    // Staff (users) management
    Route::name('admin.users.')->group(function () {
        Route::get('/admin/users/data', [AdminUserController::class, 'data'])->middleware('permission:view staffs')->name('data');
        Route::get('/admin/users/stats', [AdminUserController::class, 'getStats'])->middleware('permission:view staffs')->name('stats');
        Route::get('/admin/users', [AdminUserController::class, 'index'])->middleware('permission:view staffs')->name('index');
        
        // Base permissions applied to all staff via Role "Nhân viên"
        Route::get('/admin/users/base-permissions', [AdminUserController::class, 'editBasePermissions'])->middleware('permission:manage permissions')->name('base-permissions.edit');
        Route::put('/admin/users/base-permissions', [AdminUserController::class, 'updateBasePermissions'])->middleware('permission:manage permissions')->name('base-permissions.update');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->middleware('permission:create staffs')->name('store');
        Route::get('/admin/users/{id}', [AdminUserController::class, 'show'])->middleware('permission:view staffs')->name('show');
        Route::get('/admin/users/{id}/permissions', [AdminUserController::class, 'editPermissions'])->middleware('permission:manage permissions')->name('permissions.edit');
        Route::put('/admin/users/{id}/permissions', [AdminUserController::class, 'updatePermissions'])->middleware('permission:manage permissions')->name('permissions.update');
        Route::put('/admin/users/update', [AdminUserController::class, 'update'])->middleware('permission:edit staffs')->name('update');
        Route::post('/admin/users/toggle-status', [AdminUserController::class, 'toggleStatus'])->middleware('permission:edit staffs')->name('toggle-status');
        Route::post('/admin/users/update-role', [AdminUserController::class, 'updateRole'])->middleware('permission:edit staffs')->name('update-role');
        Route::delete('/admin/users/delete', [AdminUserController::class, 'destroy'])->middleware('permission:delete staffs')->name('destroy');
    });

    // Admin Profile management
    Route::name('admin.profile.')->group(function () {
        Route::get('/admin/profile', [AdminProfileController::class, 'index'])->name('index');
        Route::put('/admin/profile', [AdminProfileController::class, 'update'])->name('update');
        Route::get('/admin/profile/change-password', [AdminProfileController::class, 'changePasswordForm'])->name('change-password');
        Route::put('/admin/profile/change-password', [AdminProfileController::class, 'changePassword'])->name('change-password.update');
        Route::get('/admin/profile/settings', [AdminProfileController::class, 'settings'])->name('settings');
        Route::put('/admin/profile/settings', [AdminProfileController::class, 'updateSettings'])->name('settings.update');
    });
        // Promotion management
    Route::name('admin.promotions.')->group(function () {
        Route::get('/admin/promotions/data', [AdminPromotionController::class, 'data'])->middleware('permission:view promotions')->name('data');
        Route::get('/admin/promotions', [AdminPromotionController::class, 'index'])->middleware('permission:view promotions')->name('index');
        Route::get('/admin/promotions/create', [AdminPromotionController::class, 'create'])->middleware('permission:create promotions')->name('create');
        Route::post('/admin/promotions', [AdminPromotionController::class, 'store'])->middleware('permission:create promotions')->name('store');
        Route::get('/admin/promotions/{id}/edit', [AdminPromotionController::class, 'edit'])->middleware('permission:edit promotions')->name('edit');
        Route::put('/admin/promotions/update', [AdminPromotionController::class, 'update'])->middleware('permission:edit promotions')->name('update');
        Route::delete('/admin/promotions/delete', [AdminPromotionController::class, 'destroy'])->middleware('permission:delete promotions')->name('destroy');
        Route::delete('/admin/promotions/delete-multiple', [AdminPromotionController::class, 'destroyMultiple'])->middleware('permission:delete promotions')->name('destroy.multiple');
        Route::get('/admin/promotions/export/excel', [AdminPromotionController::class, 'exportExcel'])->middleware('permission:view promotions')->name('export.excel');
        Route::get('/admin/promotions/export/pdf', [AdminPromotionController::class, 'exportPdf'])->middleware('permission:view promotions')->name('export.pdf');
    });

    // Shipping Fee management
    Route::name('admin.shipping-fees.')->group(function () {
        Route::get('/admin/shipping-fees/data', [AdminShippingFeeController::class, 'data'])->middleware('permission:view shipping fees')->name('data');
        Route::get('/admin/shipping-fees', [AdminShippingFeeController::class, 'index'])->middleware('permission:view shipping fees')->name('index');
        Route::post('/admin/shipping-fees', [AdminShippingFeeController::class, 'store'])->middleware('permission:create shipping fees')->name('store');
        Route::put('/admin/shipping-fees/update', [AdminShippingFeeController::class, 'update'])->middleware('permission:edit shipping fees')->name('update');
        Route::delete('/admin/shipping-fees/delete', [AdminShippingFeeController::class, 'destroy'])->middleware('permission:delete shipping fees')->name('destroy');
        Route::delete('/admin/shipping-fees/delete-multiple', [AdminShippingFeeController::class, 'destroyMultiple'])->middleware('permission:delete shipping fees')->name('destroy.multiple');
    });
});
require __DIR__ . '/auth.php';
