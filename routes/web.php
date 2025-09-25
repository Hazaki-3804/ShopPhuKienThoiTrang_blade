<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReviewController;

Route::get('/', fn()=>redirect()->route('home'));

// Shop routes
Route::get('/home', fn()=>view('home'))->name('home');
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
Route::get('/about', fn()=>view('pages.about'))->name('about');
Route::get('/contact', fn()=>view('pages.contact'))->name('contact');

// Auth pages & logic
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/password/forgot', [AuthController::class, 'forgotForm'])->name('password.request');
Route::post('/password/forgot', [AuthController::class, 'forgotSend'])->name('password.email');
Route::get('/password/reset/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
Route::get('/password/change', [AuthController::class, 'changePasswordForm'])->middleware('auth')->name('password.change');
Route::post('/password/change', [AuthController::class, 'changePassword'])->middleware('auth')->name('password.change.post');

// OAuth mock routes (tích hợp thật dùng Socialite)
use App\Http\Controllers\Auth\SocialController;
Route::get('/oauth/{provider}', [SocialController::class, 'redirect'])->name('oauth.redirect');
Route::get('/oauth/{provider}/callback', [SocialController::class, 'callback'])->name('oauth.callback');

// Admin
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/dashboard', fn()=>view('admin.dashboard'))->name('dashboard');
    Route::get('/orders', fn()=>view('admin.orders.index'))->name('orders.index');
    Route::get('/products', fn()=>view('admin.products.index'))->name('products.index');
    Route::get('/customers', fn()=>view('admin.customers.index'))->name('customers.index');
    Route::get('/analytics', fn()=>view('admin.analytics'))->name('analytics');
    Route::get('/settings', fn()=>view('admin.settings'))->name('settings');
});
