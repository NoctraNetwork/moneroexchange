<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VendorProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'Authentication required');
        }

        // Check if user is active
        if (!$user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your account is inactive.');
        }

        // Check if user has vendor permissions
        if (!$this->hasVendorPermissions($user, $request)) {
            Log::warning('User without vendor permissions attempted vendor access', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'Vendor privileges required');
        }

        // Check for vendor-specific security requirements
        if (!$this->meetsVendorSecurityRequirements($user, $request)) {
            return redirect()->route('dashboard')->with('error', 'Please complete your vendor verification first.');
        }

        // Check for suspicious vendor activity
        if ($this->isSuspiciousVendorActivity($request, $user)) {
            Log::warning('Suspicious vendor activity detected', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
        }

        // Log vendor access
        $this->logVendorAccess($request, $user);

        return $next($request);
    }

    /**
     * Check if user has vendor permissions
     */
    private function hasVendorPermissions($user, Request $request): bool
    {
        // Check if user is admin (admins have vendor permissions)
        if ($user->is_admin) {
            return true;
        }

        // Check if user has active offers or trades
        $hasActiveOffers = $user->offers()->where('status', 'active')->exists();
        $hasActiveTrades = $user->buyerTrades()->whereIn('state', ['pending', 'in_progress'])->exists() ||
                          $user->sellerTrades()->whereIn('state', ['pending', 'in_progress'])->exists();

        // Check if accessing vendor-specific routes
        $vendorRoutes = [
            'offers.create',
            'offers.store',
            'offers.edit',
            'offers.update',
            'offers.destroy',
            'trades.seller',
            'trades.release',
            'trades.refund',
        ];

        $routeName = $request->route()?->getName();
        $isVendorRoute = $routeName && in_array($routeName, $vendorRoutes);

        // User needs either active offers/trades or be accessing vendor routes
        return ($hasActiveOffers || $hasActiveTrades) || $isVendorRoute;
    }

    /**
     * Check if user meets vendor security requirements
     */
    private function meetsVendorSecurityRequirements($user, Request $request): bool
    {
        // For sensitive vendor operations, require additional verification
        $sensitiveVendorRoutes = [
            'offers.store',
            'offers.update',
            'trades.release',
            'trades.refund',
        ];

        $routeName = $request->route()?->getName();
        
        if ($routeName && in_array($routeName, $sensitiveVendorRoutes)) {
            // Require PIN verification for sensitive operations
            if (!$request->session()->get('pin_verified', false)) {
                return false;
            }

            // Require PGP verification for high-value operations
            if ($this->isHighValueOperation($request) && !$user->hasVerifiedPgp()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the operation involves high value
     */
    private function isHighValueOperation(Request $request): bool
    {
        $amount = $request->input('amount', 0);
        $maxAmount = $request->input('max_amount', 0);
        
        // Consider it high value if amount > 1 XMR or max_amount > 1 XMR
        $highValueThreshold = 1000000000000; // 1 XMR in atomic units
        
        return $amount > $highValueThreshold || $maxAmount > $highValueThreshold;
    }

    /**
     * Check for suspicious vendor activity
     */
    private function isSuspiciousVendorActivity(Request $request, $user): bool
    {
        // Check for rapid offer creation/modification
        if ($this->isOfferOperation($request)) {
            $key = "vendor_offers:{$user->id}:" . $request->ip();
            $operations = cache()->get($key, 0);
            
            if ($operations > 10) { // More than 10 offer operations in 1 hour
                return true;
            }

            cache()->put($key, $operations + 1, 3600);
        }

        // Check for unusual trading patterns
        if ($this->isTradeOperation($request)) {
            $key = "vendor_trades:{$user->id}:" . $request->ip();
            $operations = cache()->get($key, 0);
            
            if ($operations > 20) { // More than 20 trade operations in 1 hour
                return true;
            }

            cache()->put($key, $operations + 1, 3600);
        }

        return false;
    }

    /**
     * Check if the request is an offer operation
     */
    private function isOfferOperation(Request $request): bool
    {
        $offerRoutes = [
            'offers.store',
            'offers.update',
            'offers.destroy',
        ];

        $routeName = $request->route()?->getName();
        
        return $routeName && in_array($routeName, $offerRoutes);
    }

    /**
     * Check if the request is a trade operation
     */
    private function isTradeOperation(Request $request): bool
    {
        $tradeRoutes = [
            'trades.store',
            'trades.release',
            'trades.refund',
            'trades.dispute',
        ];

        $routeName = $request->route()?->getName();
        
        return $routeName && in_array($routeName, $tradeRoutes);
    }

    /**
     * Log vendor access
     */
    private function logVendorAccess(Request $request, $user): void
    {
        try {
            \App\Models\AuditLog::create([
                'user_id' => $user->id,
                'action' => 'vendor_access',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'data' => $request->except(['password', 'pin', '_token']),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log vendor access', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }
}
