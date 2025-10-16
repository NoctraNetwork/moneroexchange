<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\PinService;
use App\Services\PgpService;
use App\Models\User;
use App\Models\UserSecurityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private PinService $pinService;
    private PgpService $pgpService;

    public function __construct(PinService $pinService, PgpService $pgpService)
    {
        $this->pinService = $pinService;
        $this->pgpService = $pgpService;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        
        // Check rate limiting
        if (!$this->checkLoginRateLimit($request)) {
            return back()->withErrors([
                'username' => 'Too many login attempts. Please try again later.'
            ]);
        }

        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Log security event
            $this->logSecurityEvent($user, $request);
            
            // Check if PIN verification is required
            if (config('auth.require_pin_on_login', true)) {
                return redirect()->route('pin.verify');
            }
            
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Log failed attempt
        Log::warning('Failed login attempt', [
            'username' => $credentials['username'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Log logout
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
            ]);
        }

        return redirect('/');
    }

    /**
     * Check login rate limit
     */
    private function checkLoginRateLimit(Request $request): bool
    {
        $key = 'login_attempts:' . $request->ip();
        $maxAttempts = config('auth.login_max_attempts', 10);
        $decayMinutes = config('auth.login_lockout_minutes', 15);
        
        return RateLimiter::attempt($key, $maxAttempts, function () {}, $decayMinutes * 60);
    }

    /**
     * Log security event
     */
    private function logSecurityEvent(User $user, Request $request): void
    {
        try {
            UserSecurityLog::create([
                'user_id' => $user->id,
                'ip_hash' => hash('sha256', $request->ip()),
                'ua_hash' => hash('sha256', $request->userAgent() ?? ''),
                'is_tor' => $this->isTorRequest($request),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log security event', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Check if request is from Tor
     */
    private function isTorRequest(Request $request): bool
    {
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        
        // Check for Tor browser user agent patterns
        $torUserAgents = [
            'Mozilla/5.0 (Windows NT 10.0; rv:102.0) Gecko/20100101 Firefox/102.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:102.0) Gecko/20100101 Firefox/102.0',
        ];
        
        if (in_array($userAgent, $torUserAgents)) {
            return true;
        }
        
        // Check for .onion domain
        if (str_contains($request->getHost(), '.onion')) {
            return true;
        }
        
        return false;
    }
}
