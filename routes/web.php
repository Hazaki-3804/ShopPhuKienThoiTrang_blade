<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Auth\SocialController;

Route::get('/', fn() => redirect()->route('home'));

// Shop routes
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{id}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/buy-now/{id}', [CartController::class, 'buyNow'])->name('cart.buynow');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/place', [CheckoutController::class, 'place'])->name('checkout.place');
Route::post('/reviews/{productId}', [ReviewController::class, 'store'])->name('reviews.store');
Route::get('/about', fn() => view('pages.about'))->name('about');
Route::get('/contact', fn() => view('pages.contact'))->name('contact');


// Admin routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/orders', fn() => view('admin.orders.index'))->name('orders.index');
    Route::get('/customers', fn() => view('admin.customers.index'))->name('customers.index');
    Route::get('/analytics', fn() => view('admin.analytics'))->name('analytics');
    Route::get('/settings', fn() => view('admin.settings'))->name('settings');

    // User management

    // Product management
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/data', [ProductController::class, 'data'])->name('data');
        Route::get('/products', [ProductController::class, 'index'])->name('index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('create');
        Route::post('/products', [ProductController::class, 'store'])->name('store');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('destroy');
    });
});
require __DIR__ . '/auth.php';
