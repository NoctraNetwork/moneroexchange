<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\PinService;
use Symfony\Component\HttpFoundation\Response;

class PinRequiredMiddleware
{
    private PinService $pinService;

    public function __construct(PinService $pinService)
    {
        $this->pinService = $pinService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if PIN is required for this action
        if (!$this->isPinRequired($request)) {
            return $next($request);
        }

        // Check if PIN is already verified in this session
        if ($this->isPinVerified($request)) {
            return $next($request);
        }

        // Check if PIN is locked
        if ($this->pinService->isPinLocked($user)) {
            $lockoutTime = $this->pinService->getLockoutTimeRemaining($user);
            return redirect()->route('pin.locked')->with('lockout_time', $lockoutTime);
        }

        // Redirect to PIN verification
        return redirect()->route('pin.verify')->with('intended', $request->url());
    }

    /**
     * Check if PIN is required for this request
     */
    private function isPinRequired(Request $request): bool
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }

        $routeName = $route->getName();
        $pinRequiredRoutes = [
            'trades.create',
            'trades.release',
            'trades.refund',
            'withdrawals.create',
            'settings.password.change',
            'settings.pin.change',
            'settings.pgp.enable',
        ];

        return in_array($routeName, $pinRequiredRoutes);
    }

    /**
     * Check if PIN is already verified in this session
     */
    private function isPinVerified(Request $request): bool
    {
        return $request->session()->get('pin_verified', false);
    }
}

