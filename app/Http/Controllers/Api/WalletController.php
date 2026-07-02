<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get user's wallet balance.
     */
    public function balance(Request $request): JsonResponse
    {
        $wallet = $request->user()->getWalletInstance();

        return response()->json([
            'success' => true,
            'message' => 'Wallet balance retrieved successfully',
            'data' => [
                'balance' => (float) $wallet->balance
            ]
        ]);
    }

    /**
     * Get user's wallet transactions.
     */
    public function transactions(Request $request): JsonResponse
    {
        $wallet = $request->user()->getWalletInstance();
        $transactions = $wallet->transactions()
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Wallet transactions retrieved successfully',
            'data' => $transactions
        ]);
    }
}
