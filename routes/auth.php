<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialController;
use Illuminate\Support\Facades\Route;
// Login routes
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
// Register routes
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
// Email verification after registration
Route::get('/verify-email', [AuthController::class, 'verifyEmailForm'])->name('verify.email.form');
Route::post('/verify-email', [AuthController::class, 'verifyEmailSubmit'])->name('verify.email.submit');
Route::post('/verify-email/resend', [AuthController::class, 'resendVerifyEmail'])->name('verify.email.resend');
// Logout route
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Password reset routes
Route::get('/password/forgot', [AuthController::class, 'forgotForm'])->name('password.request');
Route::post('/password/forgot', [AuthController::class, 'forgotSend'])->name('password.email');
Route::get('/password/reset/{token}', [AuthController::class, 'resetForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
Route::get('/password/change', [AuthController::class, 'changePasswordForm'])->middleware('auth')->name('password.change');
Route::post('/password/change', [AuthController::class, 'changePassword'])->middleware('auth')->name('password.change.post');

// OAuth routes
Route::get('/api/login/{provider}/redirect', [SocialController::class, 'redirect'])->name('oauth.redirect');
Route::get('api/login/{provider}/callback', [SocialController::class, 'callback'])->name('oauth.callback');
