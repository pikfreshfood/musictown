<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\NcoinController;
use App\Http\Controllers\PaystackWebhookController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/signup', 'auth.signup')->name('signup');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/signup', [AuthController::class, 'register'])->name('signup.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/paystack/webhook', [PaystackWebhookController::class, 'handle'])->name('paystack.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/withdrawal', [ProfileController::class, 'withdrawal'])->name('profile.withdrawal');
    Route::get('/banks', [ProfileController::class, 'banks'])->name('banks');
    Route::post('/profile/withdraw', [ProfileController::class, 'withdraw'])->name('profile.withdraw');
    Route::get('/profile/password', [ProfileController::class, 'passwordForm'])->name('profile.password.form');
    Route::get('/profile/upgrade', [ProfileController::class, 'upgradeForm'])->name('profile.upgrade.form');
    Route::post('/profile/upgrade', [ProfileController::class, 'upgrade'])->name('profile.upgrade');
    Route::post('/profile/upgrade/check', [ProfileController::class, 'checkUpgradePayment'])->name('profile.upgrade.check');
    Route::get('/profile/history', [ProfileController::class, 'history'])->name('profile.history');
    Route::get('/profile/referrals', [ProfileController::class, 'referrals'])->name('profile.referrals');
    Route::get('/profile/withdrawal/receipt/{withdrawal}', [ProfileController::class, 'receipt'])->name('profile.withdrawal.receipt');
    Route::post('/verify-account', [ProfileController::class, 'verifyAccount'])->name('verify.account');
    Route::post('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');

    Route::get('/premium', [PremiumController::class, 'index'])->name('premium.index');
    Route::post('/premium/pay', [PremiumController::class, 'submitPayment'])->name('premium.submit');

    Route::get('/premium/pay-ncoin', [NcoinController::class, 'payForm'])->name('premium.pay-ncoin');
    Route::post('/premium/pay-ncoin/submit', [NcoinController::class, 'submitPayment'])->name('premium.ncoin.submit');
    Route::get('/premium/payment-submitted', [NcoinController::class, 'submitted'])->name('premium.payment-submitted');

    Route::post('/music/play/{song}', [MusicController::class, 'play']);
    Route::post('/music/tick/{song}', [MusicController::class, 'tick']);
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminController::class, 'loginForm'])->name('admin.login');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login.submit');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/users/delete/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
        Route::get('/users/{id}/referrals', [AdminController::class, 'userReferrals'])->name('admin.users.referrals');
        Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('admin.users.show');

        Route::get('/music', [AdminController::class, 'music'])->name('admin.music');
        Route::post('/music/upload', [AdminController::class, 'uploadMusic'])->name('admin.upload.music');
        Route::post('/music/delete-all', [AdminController::class, 'deleteAllMusic'])->name('admin.music.delete-all');
        Route::get('/music/delete/{id}', [AdminController::class, 'deleteMusic'])->name('admin.music.delete');

        Route::get('/sub-admins', [AdminController::class, 'subAdmins'])->name('admin.sub-admins');
        Route::post('/sub-admins/create', [AdminController::class, 'createSubAdmin'])->name('admin.sub-admins.create');
        Route::get('/sub-admins/delete/{id}', [AdminController::class, 'deleteSubAdmin'])->name('admin.sub-admins.delete');

        // Finance routes removed per request
        // Route::get('/ncoin-payments', [AdminController::class, 'ncoinPayments'])->name('admin.ncoin-payments');
        // Route::get('/ncoin-payments/approve/{payment}', [AdminController::class, 'approveNcoin'])->name('admin.ncoin.approve');
        // Route::get('/ncoin-payments/reject/{payment}', [AdminController::class, 'rejectNcoin'])->name('admin.ncoin.reject');

        // Route::get('/ncoin-codes', [AdminController::class, 'ncoinCodes'])->name('admin.ncoin-codes');
        // Route::post('/ncoin-codes/generate', [AdminController::class, 'generateNcoinCode'])->name('admin.ncoin-codes.generate');
        // Route::get('/ncoin-codes/delete/{id}', [AdminController::class, 'deleteNcoinCode'])->name('admin.ncoin-codes.delete');

        // Route::get('/payments', [AdminController::class, 'payments'])->name('admin.payments');
        // Route::get('/payments/approve/{payment}', [AdminController::class, 'approvePremium'])->name('admin.premium.approve');
        // Route::get('/payments/reject/{payment}', [AdminController::class, 'rejectPremium'])->name('admin.premium.reject');

        // Route::get('/payment-account', [AdminController::class, 'paymentAccount'])->name('admin.payment-account');
        // Route::post('/payment-account/save', [AdminController::class, 'savePaymentAccount'])->name('admin.payment-account.save');

        Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/settings/password', [AdminController::class, 'updatePassword'])->name('admin.settings.password');
    });
});
