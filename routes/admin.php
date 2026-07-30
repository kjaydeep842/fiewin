<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\GameManagerController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// User Management
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

// Game Management
Route::get('/games', [GameManagerController::class, 'index'])->name('games.index');
Route::post('/games/{game}/rtp', [GameManagerController::class, 'updateRTP'])->name('games.rtp');
Route::post('/games/{game}/toggle', [GameManagerController::class, 'toggleActive'])->name('games.toggle');
Route::post('/games/{game}/override', [GameManagerController::class, 'overrideResult'])->name('games.override');

// Financial Management
Route::get('/deposits', [FinancialController::class, 'deposits'])->name('deposits.index');
Route::post('/deposits/{deposit}/approve', [FinancialController::class, 'approveDeposit'])->name('deposits.approve');

Route::get('/withdrawals', [FinancialController::class, 'withdrawals'])->name('withdrawals.index');
Route::post('/withdrawals/{withdrawal}/approve', [FinancialController::class, 'approveWithdrawal'])->name('withdrawals.approve');

// Reports
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

// Admin Logout
Route::post('/logout', function (Request $request) {
    auth()->guard('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/admin/login')->with('success', 'You have been logged out.');
})->name('logout');
