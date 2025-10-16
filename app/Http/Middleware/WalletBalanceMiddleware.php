<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\WalletBalanceService;
use Illuminate\Support\Facades\Auth;

class WalletBalanceMiddleware
{
    protected $walletBalanceService;

    public function __construct(WalletBalanceService $walletBalanceService)
    {
        $this->walletBalanceService = $walletBalanceService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'sell')
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Only validate for sell offers and trades
        if ($type === 'sell') {
            $amount = $this->getAmountFromRequest($request);
            
            if ($amount > 0) {
                $validation = $this->walletBalanceService->validateOfferAmount($user, $amount, 'sell');
                
                if (!$validation['valid']) {
                    return redirect()->back()
                        ->withErrors([
                            'amount' => $validation['error'],
                            'available_balance' => $validation['available_balance']
                        ])
                        ->withInput();
                }
            }
        }

        return $next($request);
    }

    /**
     * Extract amount from request based on route
     */
    protected function getAmountFromRequest(Request $request): float
    {
        // For offer creation
        if ($request->has('amount')) {
            return (float) $request->input('amount');
        }

        // For trade creation
        if ($request->has('trade_amount')) {
            return (float) $request->input('trade_amount');
        }

        // For offer updates
        if ($request->has('offer.amount')) {
            return (float) $request->input('offer.amount');
        }

        return 0.0;
    }
}
