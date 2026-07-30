<?php

use App\Http\Controllers\Api\GameApiController;
use App\Http\Controllers\Api\WalletApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/games', [GameApiController::class, 'getGames']);
    Route::get('/games/{code}/history', [GameApiController::class, 'getGameHistory']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/games/bet', [GameApiController::class, 'placeBet']);
        Route::get('/wallet/balance', [WalletApiController::class, 'getBalance']);
        Route::get('/wallet/transactions', [WalletApiController::class, 'getTransactions']);
    });
});
