<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\Offer;
use App\Services\EscrowService;
use App\Services\MoneroRpcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    private EscrowService $escrowService;
    private MoneroRpcService $moneroService;

    public function __construct(EscrowService $escrowService, MoneroRpcService $moneroService)
    {
        $this->escrowService = $escrowService;
        $this->moneroService = $moneroService;
        
        $this->middleware(['auth', 'user.protect']);
    }

    /**
     * Display a listing of trades
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $trades = Trade::byUser($user->id)
            ->with(['buyer', 'seller', 'offer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('trades.index', compact('trades'));
    }

    /**
     * Show the form for creating a new trade
     */
    public function create(Offer $offer)
    {
        $user = Auth::user();
        
        // Check if user can create trade
        if ($user->id === $offer->user_id) {
            return redirect()->back()->with('error', 'You cannot trade with your own offer');
        }

        if (!$offer->isActive()) {
            return redirect()->back()->with('error', 'This offer is no longer active');
        }

        return view('trades.create', compact('offer'));
    }

    /**
     * Store a newly created trade
     */
    public function store(Request $request, Offer $offer)
    {
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'amount_xmr' => 'required|numeric|min:0.001|max:100',
            'buyer_address' => 'required|string|min:95|max:95',
        ]);

        // Check if user can create trade
        if ($user->id === $offer->user_id) {
            return redirect()->back()->with('error', 'You cannot trade with your own offer');
        }

        if (!$offer->isActive()) {
            return redirect()->back()->with('error', 'This offer is no longer active');
        }

        // Convert XMR to atomic units
        $amountAtomic = intval($request->amount_xmr * 1e12);
        
        // Validate amount is within offer limits
        if ($amountAtomic < $offer->getMinXmr() || $amountAtomic > $offer->getMaxXmr()) {
            return redirect()->back()->with('error', 'Amount is outside the offer limits');
        }

        // Validate Monero address
        if (!$this->isValidMoneroAddress($request->buyer_address)) {
            return redirect()->back()->with('error', 'Invalid Monero address');
        }

        try {
            DB::beginTransaction();

            // Create trade
            $trade = Trade::create([
                'buyer_id' => $user->id,
                'seller_id' => $offer->user_id,
                'offer_id' => $offer->id,
                'state' => 'draft',
                'amount_atomic' => $amountAtomic,
                'price_per_xmr' => $offer->getCurrentPrice(),
                'currency' => $offer->currency,
                'buyer_address' => $request->buyer_address,
                'expires_at' => now()->addHours(24), // 24 hour expiration
            ]);

            // Create escrow subaddress
            $escrowAddress = $this->escrowService->createEscrowSubaddress($trade);
            
            if (!$escrowAddress) {
                throw new \Exception('Failed to create escrow subaddress');
            }

            // Update trade state
            $trade->update(['state' => 'await_deposit']);

            // Add event
            $trade->addEvent('trade_created', $user->id, [
                'amount_xmr' => $request->amount_xmr,
                'amount_atomic' => $amountAtomic,
                'buyer_address' => $request->buyer_address,
                'escrow_address' => $escrowAddress
            ]);

            DB::commit();

            return redirect()->route('trades.show', $trade)
                ->with('success', 'Trade created successfully. Please send XMR to the escrow address.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create trade', [
                'user_id' => $user->id,
                'offer_id' => $offer->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to create trade. Please try again.');
        }
    }

    /**
     * Display the specified trade
     */
    public function show(Trade $trade)
    {
        $user = Auth::user();
        
        // Check if user is part of this trade
        if ($trade->buyer_id !== $user->id && $trade->seller_id !== $user->id) {
            abort(403, 'Access denied');
        }

        // Check for deposits if trade is awaiting deposit
        if ($trade->isAwaitingDeposit()) {
            $this->escrowService->checkForDeposits($trade);
            $trade->refresh();
        }

        $escrowStatus = $this->escrowService->getEscrowStatus($trade);
        
        return view('trades.show', compact('trade', 'escrowStatus'));
    }

    /**
     * Release escrow funds (buyer confirms payment received)
     */
    public function release(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Only buyer can release escrow
        if ($trade->buyer_id !== $user->id) {
            abort(403, 'Only the buyer can release escrow');
        }

        // Validate PIN if required
        if (!$request->session()->get('pin_verified', false)) {
            return redirect()->route('pin.verify')
                ->with('error', 'PIN verification required to release escrow');
        }

        if (!$trade->canBeReleased()) {
            return redirect()->back()->with('error', 'Trade cannot be released at this time');
        }

        try {
            $success = $this->escrowService->releaseEscrow(
                $trade, 
                $trade->buyer_address, 
                $user->id
            );

            if ($success) {
                return redirect()->back()->with('success', 'Escrow released successfully. XMR has been sent to your address.');
            } else {
                return redirect()->back()->with('error', 'Failed to release escrow. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to release escrow', [
                'trade_id' => $trade->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to release escrow. Please contact support.');
        }
    }

    /**
     * Refund escrow funds (seller refunds)
     */
    public function refund(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Only seller can refund
        if ($trade->seller_id !== $user->id) {
            abort(403, 'Only the seller can refund escrow');
        }

        // Validate PIN if required
        if (!$request->session()->get('pin_verified', false)) {
            return redirect()->route('pin.verify')
                ->with('error', 'PIN verification required to refund escrow');
        }

        if (!$trade->canBeRefunded()) {
            return redirect()->back()->with('error', 'Trade cannot be refunded at this time');
        }

        try {
            $success = $this->escrowService->refundEscrow($trade, $user->id);

            if ($success) {
                return redirect()->back()->with('success', 'Escrow refunded successfully. XMR has been returned to your wallet.');
            } else {
                return redirect()->back()->with('error', 'Failed to refund escrow. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to refund escrow', [
                'trade_id' => $trade->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to refund escrow. Please contact support.');
        }
    }

    /**
     * Cancel trade
     */
    public function cancel(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Check if user is part of this trade
        if ($trade->buyer_id !== $user->id && $trade->seller_id !== $user->id) {
            abort(403, 'Access denied');
        }

        if (!$trade->canBeCancelled()) {
            return redirect()->back()->with('error', 'Trade cannot be cancelled at this time');
        }

        try {
            $trade->update(['state' => 'cancelled']);
            $trade->addEvent('trade_cancelled', $user->id);

            return redirect()->back()->with('success', 'Trade cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to cancel trade', [
                'trade_id' => $trade->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to cancel trade. Please try again.');
        }
    }

    /**
     * Mark payment as received (seller confirms payment)
     */
    public function confirmPayment(Request $request, Trade $trade)
    {
        $user = Auth::user();
        
        // Only seller can confirm payment
        if ($trade->seller_id !== $user->id) {
            abort(403, 'Only the seller can confirm payment');
        }

        if ($trade->state !== 'escrowed') {
            return redirect()->back()->with('error', 'Trade is not in the correct state to confirm payment');
        }

        try {
            $trade->update(['state' => 'release_pending']);
            $trade->addEvent('payment_confirmed', $user->id);

            return redirect()->back()->with('success', 'Payment confirmed. Waiting for buyer to release escrow.');
        } catch (\Exception $e) {
            Log::error('Failed to confirm payment', [
                'trade_id' => $trade->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to confirm payment. Please try again.');
        }
    }

    /**
     * Validate Monero address
     */
    private function isValidMoneroAddress(string $address): bool
    {
        // Basic Monero address validation
        // Monero addresses start with '4' or '8' and are 95 characters long
        return preg_match('/^[48][0-9A-Za-z]{94}$/', $address) === 1;
    }

    /**
     * Get trade statistics
     */
    public function statistics()
    {
        $user = Auth::user();
        
        $stats = [
            'total_trades' => Trade::byUser($user->id)->count(),
            'completed_trades' => Trade::byUser($user->id)->completed()->count(),
            'active_trades' => Trade::byUser($user->id)->active()->count(),
            'total_volume_xmr' => Trade::byUser($user->id)->completed()->sum('amount_atomic') / 1e12,
        ];

        return response()->json($stats);
    }
}
