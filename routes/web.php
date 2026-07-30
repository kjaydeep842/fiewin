<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Player\GameController;
use App\Http\Controllers\Player\HomeController;
use App\Http\Controllers\Player\ProfileController;
use App\Http\Controllers\Player\PromotionController;
use App\Http\Controllers\Player\ReferralController;
use App\Http\Controllers\Player\WalletController;
use Illuminate\Support\Facades\Route;

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Player Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Games
    Route::get('/games', [GameController::class, 'index'])->name('games.index');
    Route::get('/games/{code}', [GameController::class, 'show'])->name('games.show');
    Route::get('/games/{code}/state', [GameController::class, 'getGameState'])->name('games.state');
    Route::post('/games/bet', [GameController::class, 'placeBet'])->name('games.bet');
    Route::post('/games/mines/start', [GameController::class, 'startMines'])->name('games.mines.start');
    Route::post('/games/mines/click', [GameController::class, 'revealMinesTile'])->name('games.mines.click');
    Route::post('/games/mines/cashout', [GameController::class, 'cashoutMines'])->name('games.mines.cashout');
    Route::get('/games/mines/history', [GameController::class, 'getMinesHistory'])->name('games.mines.history');
    Route::post('/games/crash/cashout', [GameController::class, 'cashoutCrash'])->name('games.crash.cashout');
    Route::post('/games/spin/settle', [GameController::class, 'settleSpinWheel'])->name('games.spin.settle');
    Route::post('/games/dice/settle', [GameController::class, 'settleDice'])->name('games.dice.settle');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
    Route::post('/wallet/transfer-commission', [WalletController::class, 'transferCommission'])->name('wallet.transfer');

    // Referral
    Route::get('/referral', [ReferralController::class, 'index'])->name('referral.index');

    // Promotion & Daily Check-in
    Route::get('/promotion', [PromotionController::class, 'index'])->name('promotion.index');
    Route::post('/promotion/daily-checkin', [PromotionController::class, 'claimDailyCheckin'])->name('promotion.checkin');
    Route::post('/promotion/redeem-coupon', [PromotionController::class, 'redeemCoupon'])->name('promotion.coupon');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/submit-kyc', [ProfileController::class, 'submitKYC'])->name('profile.kyc');
    Route::post('/profile/add-bank', [ProfileController::class, 'addBankAccount'])->name('profile.bank');
});

// Admin Authentication (public - no admin middleware)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login']);
});
