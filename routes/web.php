<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\StatisticsController as AdminStatisticsController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\ProfileController;

Route::get('/', fn() => redirect()->route('home'));

//Home routes
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Shop routes
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{id}', [ShopController::class, 'show'])->name('shop.show');

// Cart and Checkout routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/buy-now/{id}', [CartController::class, 'buyNow'])->name('cart.buynow');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/place', [CheckoutController::class, 'place'])->name('checkout.place');

// Product reviews routes
Route::post('/reviews/{productId}', [ReviewController::class, 'store'])->name('reviews.store');

// Static pages
Route::get('/about', fn() => view('pages.about'))->name('about');
Route::get('/contact', fn() => view('pages.contact'))->name('contact');

// Chatbot
Route::post('/api/chatbot', [ChatbotController::class, 'chat'])->name('chatbot.chat');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Test route without middleware
Route::get('/dashboard-test', [AdminDashboardController::class, 'index'])->name('dashboard.test');

// Admin routes
Route::middleware(['auth', 'checkAdmin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/stats', [AdminDashboardController::class, 'getStatsApi'])->name('dashboard.stats');
    Route::get('/api/dashboard/charts', [AdminDashboardController::class, 'getChartsApi'])->name('dashboard.charts');
    Route::get('/analytics', fn() => view('admin.analytics'))->name('analytics');
    Route::get('/settings', fn() => view('admin.settings'))->name('settings');

    // Customer management
    Route::name('admin.customers.')->group(function () {
        Route::get('/admin/customers/data', [AdminCustomerController::class, 'data'])->name('data');
        Route::get('/admin/customers', [AdminCustomerController::class, 'index'])->name('index');
        Route::get('/admin/customers/{id}', [AdminCustomerController::class, 'show'])->name('show');
        Route::post('/admin/customers', [AdminCustomerController::class, 'store'])->name('store');
        Route::put('/admin/customers/update', [AdminCustomerController::class, 'update'])->name('update');
        Route::post('/admin/customers/toggle-status', [AdminCustomerController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/admin/customers/delete', [AdminCustomerController::class, 'destroy'])->name('destroy');
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
        Route::get('/admin/categories/data', [AdminCategoryController::class, 'data'])->name('data');
        Route::get('/admin/categories', [AdminCategoryController::class, 'index'])->name('index');
        Route::post('/admin/categories', [AdminCategoryController::class, 'store'])->name('store');
        Route::put('/admin/categories/update', [AdminCategoryController::class, 'update'])->name('update');
        Route::delete('/admin/categories/delete', [AdminCategoryController::class, 'destroy'])->name('destroy');
        Route::delete('/admin/categories/delete-multiple', [AdminCategoryController::class, 'destroyMultiple'])->name('destroy.multiple');
    });
    // Product management
    Route::name('admin.products.')->group(function () {
        Route::get('/admin/products/data', [AdminProductController::class, 'data'])->name('data');
        Route::get('/admin/products', [AdminProductController::class, 'index'])->name('index');
        Route::get('/admin/products/create', [AdminProductController::class, 'create'])->name('create');
        Route::post('/admin/products', [AdminProductController::class, 'store'])->name('store');
        Route::get('/admin/products/{id}', [AdminProductController::class, 'show'])->name('show');
        Route::get('/admin/products/{id}/edit', [AdminProductController::class, 'edit'])->name('edit');
        Route::put('/admin/products/update', [AdminProductController::class, 'update'])->name('update');
        Route::post('/admin/products/upload-image', [AdminProductController::class, 'uploadImage'])->name('upload-image');
        Route::post('/admin/products/clear-temp-images', [AdminProductController::class, 'clearTempImages'])->name('clear-temp-images');
        Route::delete('/admin/products/delete', [AdminProductController::class, 'destroy'])->name('destroy');
        Route::delete('/admin/products/delete-multiple', [AdminProductController::class, 'destroyMultiple'])->name('destroy.multiple');
    });

    // Order management
    Route::name('admin.orders.')->group(function () {
        Route::get('/admin/orders/data', [AdminOrderController::class, 'data'])->name('data');
        Route::get('/admin/orders', [AdminOrderController::class, 'index'])->name('index');
        Route::get('/admin/orders/{id}', [AdminOrderController::class, 'show'])->name('show');
        Route::get('/admin/orders/{id}/print', [AdminOrderController::class, 'print'])->name('print');
        Route::post('/admin/orders/update-status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
        Route::delete('/admin/orders/delete', [AdminOrderController::class, 'destroy'])->name('destroy');
    });

    // Staff (users) management
    Route::name('admin.users.')->group(function () {
        Route::get('/admin/users/data', [AdminUserController::class, 'data'])->name('data');
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('index');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('store');
        Route::get('/admin/users/{id}', [AdminUserController::class, 'show'])->name('show');
        Route::put('/admin/users/update', [AdminUserController::class, 'update'])->name('update');
        Route::post('/admin/users/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/admin/users/update-role', [AdminUserController::class, 'updateRole'])->name('update-role');
        Route::delete('/admin/users/delete', [AdminUserController::class, 'destroy'])->name('destroy');
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
});
require __DIR__ . '/auth.php';
