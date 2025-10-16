<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class OrderValidationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to trade/order related routes
        if (!$this->isOrderRelatedRoute($request)) {
            return $next($request);
        }

        // Validate order creation/modification requests
        if ($this->isOrderCreationRequest($request)) {
            $validation = $this->validateOrderRequest($request);
            
            if (!$validation['valid']) {
                Log::warning('Invalid order request blocked', [
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id,
                    'errors' => $validation['errors'],
                    'data' => $request->except(['password', 'pin', '_token'])
                ]);

                return response()->json([
                    'error' => 'Invalid order request',
                    'details' => $validation['errors']
                ], 422);
            }
        }

        return $next($request);
    }

    /**
     * Check if the request is order-related
     */
    private function isOrderRelatedRoute(Request $request): bool
    {
        $orderRoutes = [
            'trades.store',
            'trades.update',
            'offers.store',
            'offers.update',
            'withdrawals.store',
        ];

        $routeName = $request->route()?->getName();
        
        return $routeName && in_array($routeName, $orderRoutes);
    }

    /**
     * Check if the request is creating a new order
     */
    private function isOrderCreationRequest(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH']) && 
               $this->isOrderRelatedRoute($request);
    }

    /**
     * Validate order request
     */
    private function validateOrderRequest(Request $request): array
    {
        $errors = [];
        $data = $request->all();

        // Validate user authentication
        if (!$request->user()) {
            $errors[] = 'Authentication required';
            return ['valid' => false, 'errors' => $errors];
        }

        // Validate required fields based on route
        $routeName = $request->route()?->getName();
        
        switch ($routeName) {
            case 'trades.store':
                $errors = array_merge($errors, $this->validateTradeCreation($data));
                break;
            case 'offers.store':
                $errors = array_merge($errors, $this->validateOfferCreation($data));
                break;
            case 'withdrawals.store':
                $errors = array_merge($errors, $this->validateWithdrawalCreation($data));
                break;
        }

        // Validate against duplicate orders
        if ($this->isDuplicateOrder($request, $data)) {
            $errors[] = 'Duplicate order detected';
        }

        // Validate user permissions
        if (!$this->hasOrderPermissions($request, $data)) {
            $errors[] = 'Insufficient permissions for this action';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate trade creation
     */
    private function validateTradeCreation(array $data): array
    {
        $errors = [];

        if (empty($data['offer_id'])) {
            $errors[] = 'Offer ID is required';
        }

        if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Valid amount is required';
        }

        if (empty($data['payment_method'])) {
            $errors[] = 'Payment method is required';
        }

        return $errors;
    }

    /**
     * Validate offer creation
     */
    private function validateOfferCreation(array $data): array
    {
        $errors = [];

        if (empty($data['side']) || !in_array($data['side'], ['buy', 'sell'])) {
            $errors[] = 'Valid side (buy/sell) is required';
        }

        if (empty($data['currency']) || !in_array($data['currency'], ['USD', 'EUR', 'GBP', 'JPY'])) {
            $errors[] = 'Valid currency is required';
        }

        if (empty($data['price']) || !is_numeric($data['price']) || $data['price'] <= 0) {
            $errors[] = 'Valid price is required';
        }

        if (empty($data['min_amount']) || !is_numeric($data['min_amount']) || $data['min_amount'] <= 0) {
            $errors[] = 'Valid minimum amount is required';
        }

        if (empty($data['max_amount']) || !is_numeric($data['max_amount']) || $data['max_amount'] <= 0) {
            $errors[] = 'Valid maximum amount is required';
        }

        if (isset($data['min_amount']) && isset($data['max_amount']) && $data['min_amount'] >= $data['max_amount']) {
            $errors[] = 'Maximum amount must be greater than minimum amount';
        }

        return $errors;
    }

    /**
     * Validate withdrawal creation
     */
    private function validateWithdrawalCreation(array $data): array
    {
        $errors = [];

        if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors[] = 'Valid withdrawal amount is required';
        }

        if (empty($data['address']) || !$this->isValidMoneroAddress($data['address'])) {
            $errors[] = 'Valid Monero address is required';
        }

        return $errors;
    }

    /**
     * Check for duplicate orders
     */
    private function isDuplicateOrder(Request $request, array $data): bool
    {
        $user = $request->user();
        
        // Check for recent identical orders (within last 5 minutes)
        $recentOrder = \App\Models\Trade::where('buyer_id', $user->id)
            ->where('offer_id', $data['offer_id'] ?? null)
            ->where('amount', $data['amount'] ?? null)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        return $recentOrder;
    }

    /**
     * Check if user has order permissions
     */
    private function hasOrderPermissions(Request $request, array $data): bool
    {
        $user = $request->user();

        // Check if user is active
        if (!$user->isActive()) {
            return false;
        }

        // Check if user is not suspended
        if ($user->isSuspended()) {
            return false;
        }

        // Check if user has verified PGP (for certain operations)
        if (isset($data['requires_pgp']) && $data['requires_pgp'] && !$user->hasVerifiedPgp()) {
            return false;
        }

        return true;
    }

    /**
     * Validate Monero address format
     */
    private function isValidMoneroAddress(string $address): bool
    {
        // Basic Monero address validation
        // Monero addresses start with '4' or '8' and are 95 characters long
        return preg_match('/^[48][0-9A-Za-z]{94}$/', $address) === 1;
    }
}
