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

    // Dedicated Jet Game Routes
    Route::get('/games/jet', [\App\Http\Controllers\Player\JetController::class, 'show'])->name('games.jet.show');
    Route::get('/games/jet/state', [\App\Http\Controllers\Player\JetController::class, 'getJetState'])->name('games.jet.state');
    Route::get('/games/jet/status', [\App\Http\Controllers\Player\JetController::class, 'getJetState'])->name('games.jet.status');
    Route::get('/games/jet/current-round', [\App\Http\Controllers\Player\JetController::class, 'getCurrentRound'])->name('games.jet.current-round');
    Route::get('/games/jet/current-bet', [\App\Http\Controllers\Player\JetController::class, 'getCurrentBet'])->name('games.jet.current-bet');
    Route::get('/games/jet/my-orders', [\App\Http\Controllers\Player\JetController::class, 'getMyOrders'])->name('games.jet.my-orders');
    Route::post('/games/jet/bet', [\App\Http\Controllers\Player\JetController::class, 'placeBet'])->name('games.jet.bet');
    Route::post('/games/jet/cashout', [\App\Http\Controllers\Player\JetController::class, 'cashout'])->name('games.jet.cashout');
    Route::get('/games/jet/history', [\App\Http\Controllers\Player\JetController::class, 'history'])->name('games.jet.history');

    // Dedicated Crash Game Routes
    Route::get('/games/crash', [\App\Http\Controllers\Player\CrashController::class, 'show'])->name('games.crash.show');
    Route::get('/games/crash/state', [\App\Http\Controllers\Player\CrashController::class, 'getCrashState'])->name('games.crash.state');
    Route::get('/games/crash/status', [\App\Http\Controllers\Player\CrashController::class, 'getCrashState'])->name('games.crash.status');
    Route::get('/games/crash/current-round', [\App\Http\Controllers\Player\CrashController::class, 'getCurrentRound'])->name('games.crash.current-round');
    Route::get('/games/crash/current-bet', [\App\Http\Controllers\Player\CrashController::class, 'getCurrentBet'])->name('games.crash.current-bet');
    Route::get('/games/crash/my-orders', [\App\Http\Controllers\Player\CrashController::class, 'getMyOrders'])->name('games.crash.my-orders');
    Route::post('/games/crash/bet', [\App\Http\Controllers\Player\CrashController::class, 'placeBet'])->name('games.crash.bet');
    Route::post('/games/crash/cashout', [\App\Http\Controllers\Player\CrashController::class, 'cashout'])->name('games.crash.cashout');
    Route::get('/games/crash/history', [\App\Http\Controllers\Player\CrashController::class, 'history'])->name('games.crash.history');

    // Mines Routes
    Route::post('/games/mines/start', [GameController::class, 'startMines'])->name('games.mines.start');
    Route::post('/games/mines/click', [GameController::class, 'revealMinesTile'])->name('games.mines.click');
    Route::post('/games/mines/cashout', [GameController::class, 'cashoutMines'])->name('games.mines.cashout');
    Route::get('/games/mines/history', [GameController::class, 'getMinesHistory'])->name('games.mines.history');

    // Spin & Dice Routes
    Route::post('/games/spin/settle', [GameController::class, 'settleSpinWheel'])->name('games.spin.settle');
    Route::post('/games/dice/settle', [GameController::class, 'settleDice'])->name('games.dice.settle');

    // Andar Bahar Game Routes
    Route::get('/games/andar-bahar', [\App\Http\Controllers\Player\AndarBaharController::class, 'show'])->name('games.andar-bahar.show');
    Route::get('/games/andar-bahar/state', [\App\Http\Controllers\Player\AndarBaharController::class, 'getGameState'])->name('games.andar-bahar.state');
    Route::post('/games/andar-bahar/bet', [\App\Http\Controllers\Player\AndarBaharController::class, 'placeBet'])->name('games.andar-bahar.bet');
    Route::get('/games/andar-bahar/history', [\App\Http\Controllers\Player\AndarBaharController::class, 'getHistory'])->name('games.andar-bahar.history');

    // Wildcard Parameter Routes (Must be declared last)
    Route::post('/games/bet', [GameController::class, 'placeBet'])->name('games.bet');
    Route::get('/games/{code}/state', [GameController::class, 'getGameState'])->name('games.state');
    Route::get('/games/{code}', [GameController::class, 'show'])->name('games.show');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/deposit', [WalletController::class, 'deposit'])->name('wallet.deposit');
    Route::get('/wallet/deposit/checkout/{depositId}', [WalletController::class, 'checkout'])->name('wallet.deposit.checkout');
    Route::post('/wallet/deposit/proof/{depositId}', [WalletController::class, 'submitProof'])->name('wallet.deposit.proof');
    Route::get('/wallet/deposit/status/{depositId}', [WalletController::class, 'depositStatus'])->name('wallet.deposit.status');
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
