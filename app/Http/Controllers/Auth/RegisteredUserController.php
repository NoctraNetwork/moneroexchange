<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\UserSecurityLog;
use App\Services\PinService;
use App\Services\PgpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    private PinService $pinService;
    private PgpService $pgpService;

    public function __construct(PinService $pinService, PgpService $pgpService)
    {
        $this->pinService = $pinService;
        $this->pgpService = $pgpService;
    }

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Check rate limiting
        if (!$this->checkRegisterRateLimit($request)) {
            return back()->withErrors([
                'username' => 'Too many registration attempts. Please try again later.'
            ]);
        }

        try {
            // Create user
            $user = User::create([
                'username' => $request->username,
                'password_hash' => Hash::make($request->password),
                'pin_hash' => Hash::make($request->pin),
                'country' => $request->country,
                'is_tor_only' => $this->isTorRequest($request),
            ]);

            // Log security event
            $this->logSecurityEvent($user, $request);

            // Fire registered event
            event(new Registered($user));

            // Log user in
            Auth::login($user);

            // Log successful registration
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'ip' => $request->ip(),
                'is_tor' => $this->isTorRequest($request),
            ]);

            return redirect()->route('pin.verify');

        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'username' => $request->username,
                'ip' => $request->ip(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'username' => 'Registration failed. Please try again.'
            ])->onlyInput('username');
        }
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
