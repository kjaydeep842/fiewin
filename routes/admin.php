<?php

use App\Http\Controllers\Admin\BankApprovalController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\GameManagerController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/realtime-alerts', [DashboardController::class, 'realtimeAlerts'])->name('realtime-alerts');

// User Management
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
Route::post('/users/send-notification', [UserController::class, 'sendNotification'])->name('users.send-notification');

// Bank Approvals Management
Route::get('/bank-approvals', [BankApprovalController::class, 'index'])->name('bank-approvals.index');
Route::post('/bank-approvals/{bankAccount}/approve', [BankApprovalController::class, 'approve'])->name('bank-approvals.approve');
Route::post('/bank-approvals/{bankAccount}/reject', [BankApprovalController::class, 'reject'])->name('bank-approvals.reject');

// Game Management
Route::get('/games', [GameManagerController::class, 'index'])->name('games.index');
Route::post('/games/{game}/rtp', [GameManagerController::class, 'updateRTP'])->name('games.rtp');
Route::post('/games/{game}/toggle', [GameManagerController::class, 'toggleActive'])->name('games.toggle');
Route::post('/games/{game}/override', [GameManagerController::class, 'overrideResult'])->name('games.override');

// Fast Parity Game Management
Route::prefix('fast-parity')->name('fast-parity.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminFastParityController::class, 'dashboard'])->name('dashboard');
    Route::post('/rtp', [\App\Http\Controllers\Admin\AdminFastParityController::class, 'updateRTP'])->name('rtp');
    Route::post('/limits', [\App\Http\Controllers\Admin\AdminFastParityController::class, 'updateLimits'])->name('limits');
    Route::post('/override', [\App\Http\Controllers\Admin\AdminFastParityController::class, 'setOverride'])->name('override');
    Route::post('/toggle', [\App\Http\Controllers\Admin\AdminFastParityController::class, 'toggle'])->name('toggle');
});

// Parity (3-Min) Game Management
Route::prefix('parity')->name('parity.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminParityController::class, 'dashboard'])->name('dashboard');
    Route::post('/rtp', [\App\Http\Controllers\Admin\AdminParityController::class, 'updateRTP'])->name('rtp');
    Route::post('/limits', [\App\Http\Controllers\Admin\AdminParityController::class, 'updateLimits'])->name('limits');
    Route::post('/override', [\App\Http\Controllers\Admin\AdminParityController::class, 'setOverride'])->name('override');
    Route::post('/toggle', [\App\Http\Controllers\Admin\AdminParityController::class, 'toggle'])->name('toggle');
});

// Mines Game Management
Route::prefix('mines-admin')->name('mines-admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminMinesController::class, 'dashboard'])->name('dashboard');
    Route::post('/rtp', [\App\Http\Controllers\Admin\AdminMinesController::class, 'updateRTP'])->name('rtp');
    Route::post('/limits', [\App\Http\Controllers\Admin\AdminMinesController::class, 'updateLimits'])->name('limits');
    Route::post('/toggle', [\App\Http\Controllers\Admin\AdminMinesController::class, 'toggle'])->name('toggle');
});

// Spin Wheel Game Management
Route::prefix('spin-wheel')->name('spin-wheel.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminSpinWheelController::class, 'dashboard'])->name('dashboard');
    Route::post('/rtp', [\App\Http\Controllers\Admin\AdminSpinWheelController::class, 'updateRTP'])->name('rtp');
    Route::post('/limits', [\App\Http\Controllers\Admin\AdminSpinWheelController::class, 'updateLimits'])->name('limits');
    Route::post('/override', [\App\Http\Controllers\Admin\AdminSpinWheelController::class, 'setOverride'])->name('override');
    Route::post('/toggle', [\App\Http\Controllers\Admin\AdminSpinWheelController::class, 'toggle'])->name('toggle');
});

// Dice Game Management
Route::prefix('dice-admin')->name('dice-admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminDiceController::class, 'dashboard'])->name('dashboard');
    Route::post('/rtp', [\App\Http\Controllers\Admin\AdminDiceController::class, 'updateRTP'])->name('rtp');
    Route::post('/limits', [\App\Http\Controllers\Admin\AdminDiceController::class, 'updateLimits'])->name('limits');
    Route::post('/override', [\App\Http\Controllers\Admin\AdminDiceController::class, 'setOverride'])->name('override');
    Route::post('/toggle', [\App\Http\Controllers\Admin\AdminDiceController::class, 'toggle'])->name('toggle');
});

// Andar Bahar Management
Route::prefix('andar-bahar')->name('andar-bahar.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminAndarBaharController::class, 'dashboard'])->name('dashboard');
    Route::match(['get', 'post'], '/settings', [\App\Http\Controllers\Admin\AdminAndarBaharController::class, 'settings'])->name('settings');
    Route::post('/override', [\App\Http\Controllers\Admin\AdminAndarBaharController::class, 'setOverride'])->name('override');
    Route::get('/history', [\App\Http\Controllers\Admin\AdminAndarBaharController::class, 'history'])->name('history');
    Route::get('/reports', [\App\Http\Controllers\Admin\AdminAndarBaharController::class, 'reports'])->name('reports');
});

// Jet Game Management
Route::prefix('jet')->name('jet.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminJetController::class, 'dashboard'])->name('dashboard');
    Route::match(['get', 'post'], '/settings', [\App\Http\Controllers\Admin\AdminJetController::class, 'settings'])->name('settings');
    Route::post('/override', [\App\Http\Controllers\Admin\AdminJetController::class, 'setOverride'])->name('override');
    Route::get('/history', [\App\Http\Controllers\Admin\AdminJetController::class, 'history'])->name('history');
    Route::get('/reports', [\App\Http\Controllers\Admin\AdminJetController::class, 'reports'])->name('reports');
});

// Crash Game Management
Route::prefix('crash-admin')->name('crash-admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminCrashController::class, 'dashboard'])->name('dashboard');
    Route::match(['get', 'post'], '/settings', [\App\Http\Controllers\Admin\AdminCrashController::class, 'settings'])->name('settings');
    Route::post('/override', [\App\Http\Controllers\Admin\AdminCrashController::class, 'setOverride'])->name('override');
    Route::get('/history', [\App\Http\Controllers\Admin\AdminCrashController::class, 'history'])->name('history');
    Route::get('/reports', [\App\Http\Controllers\Admin\AdminCrashController::class, 'reports'])->name('reports');
});

// Merchant Account Management
Route::prefix('merchants')->name('merchants.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminMerchantController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Admin\AdminMerchantController::class, 'store'])->name('store');
    Route::post('/{merchant}/update', [\App\Http\Controllers\Admin\AdminMerchantController::class, 'update'])->name('update');
    Route::post('/{merchant}/toggle', [\App\Http\Controllers\Admin\AdminMerchantController::class, 'toggleStatus'])->name('toggle');
    Route::post('/reset-daily', [\App\Http\Controllers\Admin\AdminMerchantController::class, 'resetDailyTotals'])->name('reset-daily');
});

// Manual Deposit Verification Center
Route::prefix('manual-deposits')->name('deposits.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\AdminDepositController::class, 'index'])->name('index');
    Route::post('/{deposit}/approve', [\App\Http\Controllers\Admin\AdminDepositController::class, 'approve'])->name('approve');
    Route::post('/{deposit}/reject', [\App\Http\Controllers\Admin\AdminDepositController::class, 'reject'])->name('reject');
    Route::post('/bulk-approve', [\App\Http\Controllers\Admin\AdminDepositController::class, 'bulkApprove'])->name('bulk-approve');
    Route::post('/bulk-reject', [\App\Http\Controllers\Admin\AdminDepositController::class, 'bulkReject'])->name('bulk-reject');
    Route::get('/export', [\App\Http\Controllers\Admin\AdminDepositController::class, 'exportCSV'])->name('export');
});

// Financial Management
Route::get('/deposits', [FinancialController::class, 'deposits'])->name('deposits.index');
Route::post('/deposits/{deposit}/approve', [FinancialController::class, 'approveDeposit'])->name('deposits.approve');

Route::get('/withdrawals', [FinancialController::class, 'withdrawals'])->name('withdrawals.index');
Route::post('/withdrawals/{withdrawal}/approve', [FinancialController::class, 'approveWithdrawal'])->name('withdrawals.approve');
Route::post('/withdrawals/{withdrawal}/reject', [FinancialController::class, 'rejectWithdrawal'])->name('withdrawals.reject');

// Reports
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// Admin Logout
Route::post('/logout', function (Request $request) {
    auth()->guard('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/admin/login')->with('success', 'You have been logged out.');
})->name('logout');
