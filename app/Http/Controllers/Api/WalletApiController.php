<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;

class WalletApiController extends Controller
{
    public function getBalance(Request $request)
    {
        $wallet = $request->user()->wallet;

        return response()->json([
            'status' => 'success',
            'data' => [
                'main_balance' => (float)$wallet->main_balance,
                'bonus_balance' => (float)$wallet->bonus_balance,
                'commission_balance' => (float)$wallet->commission_balance,
                'total_balance' => (float)$wallet->total_balance,
            ],
        ]);
    }

    public function getTransactions(Request $request)
    {
        $wallet = $request->user()->wallet;
        $transactions = WalletTransaction::where('wallet_id', $wallet->id)->latest()->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $transactions,
        ]);
    }
}
