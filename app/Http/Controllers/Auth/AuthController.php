<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\PinVerifyRequest;
use App\Services\PinService;
use App\Services\PgpService;
use App\Models\User;
use App\Models\UserSecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    private PinService $pinService;
    private PgpService $pgpService;

    public function __construct(PinService $pinService, PgpService $pgpService)
    {
        $this->pinService = $pinService;
        $this->pgpService = $pgpService;
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        
        // Check rate limiting
        if (!$this->checkLoginRateLimit($request)) {
            return back()->withErrors([
                'username' => 'Too many login attempts. Please try again later.'
            ]);
        }

        // Attempt authentication
        if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password']
        ], $request->boolean('remember'))) {
            
            $user = Auth::user();
            
            // Log security event
            $this->logSecurityEvent($user, $request);
            
            // Check if PIN is required on login
            if (config('auth.require_pin_on_login', true)) {
                $request->session()->regenerate();
                return redirect()->route('pin.verify');
            }
            
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // Increment rate limiter on failed attempt
        RateLimiter::hit($this->getLoginRateLimitKey($request), 900); // 15 minutes

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.'
        ]);
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        
        // Check rate limiting
        if (!$this->checkRegisterRateLimit($request)) {
            return back()->withErrors([
                'username' => 'Too many registration attempts. Please try again later.'
            ]);
        }

        // Create user
        $user = User::create([
            'username' => $data['username'],
            'password' => $data['password'],
            'pin' => $data['pin'],
            'country' => $data['country'] ?? null,
            'is_tor_only' => $this->isTorRequest($request),
        ]);

        // Log security event
        $this->logSecurityEvent($user, $request);

        // Log in the user
        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Account created successfully!');
    }

    /**
     * Show PIN verification form
     */
    public function showPinVerify()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.pin-verify');
    }

    /**
     * Handle PIN verification
     */
    public function verifyPin(PinVerifyRequest $request)
    {
        $user = Auth::user();
        $pin = $request->validated()['pin'];

        // Check if PIN is locked
        if ($this->pinService->isPinLocked($user)) {
            $lockoutTime = $this->pinService->getLockoutTimeRemaining($user);
            return redirect()->route('pin.locked')->with('lockout_time', $lockoutTime);
        }

        // Check rate limiting
        if (!$this->pinService->checkRateLimit($user)) {
            return back()->withErrors([
                'pin' => 'Too many PIN attempts. Please try again later.'
            ]);
        }

        // Verify PIN
        if ($this->pinService->verifyPin($user, $pin)) {
            $this->pinService->resetAttempts($user);
            $request->session()->put('pin_verified', true);
            
            return redirect()->intended(route('dashboard'));
        }

        // Increment attempts
        $this->pinService->incrementAttempts($user);

        return back()->withErrors([
            'pin' => 'Invalid PIN. Please try again.'
        ]);
    }

    /**
     * Show PIN locked page
     */
    public function showPinLocked()
    {
        return view('auth.pin-locked');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
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
     * Check registration rate limit
     */
    private function checkRegisterRateLimit(Request $request): bool
    {
        $key = 'register_attempts:' . $request->ip();
        $maxAttempts = config('auth.register_max_attempts', 5);
        $decayMinutes = config('auth.register_lockout_minutes', 60);
        
        return RateLimiter::attempt($key, $maxAttempts, function () {}, $decayMinutes * 60);
    }

    /**
     * Get login rate limit key
     */
    private function getLoginRateLimitKey(Request $request): string
    {
        return 'login_attempts:' . $request->ip();
    }

    /**
     * Check if request is from Tor
     */
    private function isTorRequest(Request $request): bool
    {
        // Simple Tor detection - in production, use a more sophisticated method
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
}

