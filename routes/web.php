<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/offers', [HomeController::class, 'offers'])->name('offers');
Route::get('/offers/{offer}', [HomeController::class, 'showOffer'])->name('offers.show');
Route::get('/how-it-works', [HomeController::class, 'howItWorks'])->name('how-it-works');
Route::get('/fees', [HomeController::class, 'fees'])->name('fees');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/security', [HomeController::class, 'security'])->name('security');

// Authentication routes
Route::middleware(['guest', 'rate.limit:login'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['guest', 'rate.limit:register'])->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['auth', 'rate.limit:pin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/pin/verify', [AuthController::class, 'showPinVerify'])->name('pin.verify');
    Route::post('/pin/verify', [AuthController::class, 'verifyPin']);
    Route::get('/pin/locked', [AuthController::class, 'showPinLocked'])->name('pin.locked');
});

// Protected routes (require authentication)
Route::middleware(['auth', 'user.protect'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Trade routes
    Route::get('/trades', [App\Http\Controllers\TradeController::class, 'index'])->name('trades.index');
    Route::get('/trades/create/{offer}', [App\Http\Controllers\TradeController::class, 'create'])->name('trades.create');
    Route::post('/trades/{offer}', [App\Http\Controllers\TradeController::class, 'store'])->name('trades.store')->middleware(['order.validate', 'rate.limit:trade']);
    Route::get('/trades/{trade}', [App\Http\Controllers\TradeController::class, 'show'])->name('trades.show');
    Route::post('/trades/{trade}/release', [App\Http\Controllers\TradeController::class, 'release'])->name('trades.release')->middleware(['rate.limit:trade']);
    Route::post('/trades/{trade}/refund', [App\Http\Controllers\TradeController::class, 'refund'])->name('trades.refund')->middleware(['rate.limit:trade']);
    Route::post('/trades/{trade}/cancel', [App\Http\Controllers\TradeController::class, 'cancel'])->name('trades.cancel');
    Route::post('/trades/{trade}/confirm-payment', [App\Http\Controllers\TradeController::class, 'confirmPayment'])->name('trades.confirm-payment')->middleware(['rate.limit:trade']);
    Route::get('/trades/statistics', [App\Http\Controllers\TradeController::class, 'statistics'])->name('trades.statistics');
    
    // Feedback routes
    Route::get('/feedback', [App\Http\Controllers\FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/given', [App\Http\Controllers\FeedbackController::class, 'given'])->name('feedback.given');
    Route::get('/feedback/create/{trade}', [App\Http\Controllers\FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback/{trade}', [App\Http\Controllers\FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/users/{user}/profile', [App\Http\Controllers\FeedbackController::class, 'profile'])->name('feedback.profile');
    Route::get('/api/users/{user}/feedback', [App\Http\Controllers\FeedbackController::class, 'statistics'])->name('feedback.statistics');
    
    // Offer routes with wallet balance validation
    Route::get('/offers/create', [App\Http\Controllers\OfferController::class, 'create'])->name('offers.create')->middleware(['vendor.protect']);
    Route::post('/offers', [App\Http\Controllers\OfferController::class, 'store'])->name('offers.store')->middleware(['vendor.protect', 'order.validate', 'rate.limit:offer', 'wallet.balance:sell']);
    Route::get('/offers/{offer}/edit', [App\Http\Controllers\OfferController::class, 'edit'])->name('offers.edit')->middleware(['vendor.protect']);
    Route::put('/offers/{offer}', [App\Http\Controllers\OfferController::class, 'update'])->name('offers.update')->middleware(['vendor.protect', 'order.validate', 'rate.limit:offer', 'wallet.balance:sell']);
    
    // TODO: Add more protected routes here with proper middleware
    // Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    // Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store')->middleware(['order.validate', 'rate.limit:withdrawal']);
    // Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    // Route::post('/settings/password', [SettingsController::class, 'changePassword'])->name('settings.password.change')->middleware('rate.limit:general');
    // Route::post('/settings/pin', [SettingsController::class, 'changePin'])->name('settings.pin.change')->middleware('rate.limit:general');
    // Route::post('/settings/pgp', [SettingsController::class, 'enablePgp'])->name('settings.pgp.enable')->middleware('rate.limit:general');
});

// Admin routes (require admin role)
Route::middleware(['auth', 'admin.protect', 'rate.limit:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/rpc-config', [App\Http\Controllers\Admin\AdminController::class, 'rpcConfig'])->name('rpc-config');
    Route::put('/rpc-config', [App\Http\Controllers\Admin\AdminController::class, 'updateRpcConfig'])->name('rpc-config.update');
    Route::post('/rpc-config/test', [App\Http\Controllers\Admin\AdminController::class, 'testRpcConnection'])->name('rpc-config.test');
    Route::get('/system-settings', [App\Http\Controllers\Admin\AdminController::class, 'systemSettings'])->name('system-settings');
    Route::put('/system-settings', [App\Http\Controllers\Admin\AdminController::class, 'updateSystemSettings'])->name('system-settings.update');
});

// CSRF token refresh route
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf-token');

// API routes (read-only, cached)
Route::prefix('api/v1')->name('api.')->group(function () {
    // TODO: Add API routes here
    // Route::get('/offers', [ApiController::class, 'offers'])->name('offers');
    // Route::get('/user/{user}/rep', [ApiController::class, 'userReputation'])->name('user.reputation');
    // Route::get('/prices', [ApiController::class, 'prices'])->name('prices');
});
