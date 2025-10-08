<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Admin\AdminOrdersController;
use App\Http\Controllers\Admin\AdminReviewsController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserOrderController;

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
Route::post('/checkout/save-address', [CheckoutController::class, 'saveAddress'])->name('checkout.saveAddress');
Route::get('/checkout/payment', [CheckoutController::class, 'payment'])->name('checkout.payment');
Route::post('/checkout/place', [CheckoutController::class, 'place'])->name('checkout.place');
Route::get('/checkout/momo/return', [CheckoutController::class, 'momoReturn'])->name('checkout.momo.return');
Route::post('/checkout/momo/notify', [CheckoutController::class, 'momoNotify'])->name('checkout.momo.notify');

// Invoice routes
Route::get('/invoice/{orderId}', [InvoiceController::class, 'show'])->name('invoice.show');

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
    // User Orders overview
    Route::get('/my-orders', [UserOrderController::class, 'index'])->name('user.orders.index');
    Route::get('/my-orders/{order}', [UserOrderController::class, 'show'])->name('user.orders.show');
    Route::patch('/my-orders/{order}/cancel', [UserOrderController::class, 'cancel'])->name('user.orders.cancel');
});

// Admin routes
Route::middleware(['auth', 'checkAdmin'])->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/orders', [AdminOrdersController::class, 'index'])->name('orders.index');
    Route::patch('/orders/{order}', [AdminOrdersController::class, 'update'])->name('orders.update');
    Route::get('/analytics', fn() => view('admin.analytics'))->name('analytics');
    Route::get('/settings', fn() => view('admin.settings'))->name('settings');

    // Reviews management
    Route::get('/admin/reviews', [AdminReviewsController::class, 'index'])->name('admin.reviews.index');
    Route::patch('/admin/reviews/{review}/toggle', [AdminReviewsController::class, 'toggleVisibility'])->name('admin.reviews.toggle');
    Route::delete('/admin/reviews/{review}', [AdminReviewsController::class, 'destroy'])->name('admin.reviews.destroy');

    // Customer management (add more routes later)
    Route::name('customers.')->group(function () {
        Route::get('/customers/data', [CustomerController::class, 'data'])->name('data');
        Route::get('/customers', [CustomerController::class, 'index'])->name('index');
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('store');
        Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('update');
        Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('edit');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    // Product management
    Route::name('products.')->group(function () {
        Route::get('/data', [ProductController::class, 'data'])->name('data');
        Route::get('/products', [ProductController::class, 'index'])->name('index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('create');
        Route::post('/products', [ProductController::class, 'store'])->name('store');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });
});
require __DIR__ . '/auth.php';
