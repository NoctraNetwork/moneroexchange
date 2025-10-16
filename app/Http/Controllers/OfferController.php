<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\PaymentMethod;
use App\Services\PriceIndexService;
use App\Services\WalletBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    private PriceIndexService $priceService;
    private WalletBalanceService $walletService;

    public function __construct(PriceIndexService $priceService, WalletBalanceService $walletService)
    {
        $this->priceService = $priceService;
        $this->walletService = $walletService;
        
        $this->middleware(['auth', 'user.protect']);
    }

    /**
     * Show the form for creating a new offer
     */
    public function create()
    {
        $paymentMethods = PaymentMethod::active()->get();
        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD'];
        
        return view('offers.create', compact('paymentMethods', 'currencies'));
    }

    /**
     * Store a newly created offer
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'side' => 'required|in:buy,sell',
            'currency' => 'required|string|in:USD,EUR,GBP,JPY,CAD,AUD',
            'price_type' => 'required|in:fixed,floating',
            'price' => 'required_if:price_type,fixed|numeric|min:0.01',
            'margin' => 'required_if:price_type,floating|numeric|min:-50|max:50',
            'min_xmr' => 'required|numeric|min:0.001|max:100',
            'max_xmr' => 'required|numeric|min:0.001|max:100|gte:min_xmr',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'country' => 'nullable|string|size:2',
            'online_or_inperson' => 'required|in:online,inperson',
            'terms' => 'required|string|max:1000',
            'min_trade_time' => 'required|integer|min:0|max:60',
            'max_trade_time' => 'required|integer|min:0|max:1440|gte:min_trade_time',
        ]);

        // Check if user has sufficient balance for sell offers
        if ($request->side === 'sell') {
            $maxAmountAtomic = intval($request->max_xmr * 1e12);
            if (!$this->walletService->hasSufficientBalance($user, $maxAmountAtomic)) {
                return redirect()->back()->with('error', 'Insufficient wallet balance for this offer');
            }
        }

        try {
            DB::beginTransaction();

            // Create offer
            $offer = Offer::create([
                'user_id' => $user->id,
                'side' => $request->side,
                'currency' => $request->currency,
                'price_type' => $request->price_type,
                'price' => $request->price ?? 0,
                'margin' => $request->margin ?? 0,
                'min_xmr_atomic' => intval($request->min_xmr * 1e12),
                'max_xmr_atomic' => intval($request->max_xmr * 1e12),
                'payment_method_id' => $request->payment_method_id,
                'country' => $request->country,
                'online_or_inperson' => $request->online_or_inperson,
                'terms' => $request->terms,
                'min_trade_time' => $request->min_trade_time,
                'max_trade_time' => $request->max_trade_time,
                'state' => 'active',
            ]);

            DB::commit();

            return redirect()->route('offers.show', $offer)
                ->with('success', 'Offer created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create offer', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to create offer. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified offer
     */
    public function edit(Offer $offer)
    {
        $user = Auth::user();
        
        // Check if user owns this offer
        if ($offer->user_id !== $user->id) {
            abort(403, 'Access denied');
        }

        if (!$offer->canBeEdited()) {
            return redirect()->back()->with('error', 'This offer cannot be edited');
        }

        $paymentMethods = PaymentMethod::active()->get();
        $currencies = ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD'];
        
        return view('offers.edit', compact('offer', 'paymentMethods', 'currencies'));
    }

    /**
     * Update the specified offer
     */
    public function update(Request $request, Offer $offer)
    {
        $user = Auth::user();
        
        // Check if user owns this offer
        if ($offer->user_id !== $user->id) {
            abort(403, 'Access denied');
        }

        if (!$offer->canBeEdited()) {
            return redirect()->back()->with('error', 'This offer cannot be edited');
        }

        // Validate request
        $request->validate([
            'side' => 'required|in:buy,sell',
            'currency' => 'required|string|in:USD,EUR,GBP,JPY,CAD,AUD',
            'price_type' => 'required|in:fixed,floating',
            'price' => 'required_if:price_type,fixed|numeric|min:0.01',
            'margin' => 'required_if:price_type,floating|numeric|min:-50|max:50',
            'min_xmr' => 'required|numeric|min:0.001|max:100',
            'max_xmr' => 'required|numeric|min:0.001|max:100|gte:min_xmr',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'country' => 'nullable|string|size:2',
            'online_or_inperson' => 'required|in:online,inperson',
            'terms' => 'required|string|max:1000',
            'min_trade_time' => 'required|integer|min:0|max:60',
            'max_trade_time' => 'required|integer|min:0|max:1440|gte:min_trade_time',
        ]);

        // Check if user has sufficient balance for sell offers
        if ($request->side === 'sell') {
            $maxAmountAtomic = intval($request->max_xmr * 1e12);
            if (!$this->walletService->hasSufficientBalance($user, $maxAmountAtomic)) {
                return redirect()->back()->with('error', 'Insufficient wallet balance for this offer');
            }
        }

        try {
            DB::beginTransaction();

            // Update offer
            $offer->update([
                'side' => $request->side,
                'currency' => $request->currency,
                'price_type' => $request->price_type,
                'price' => $request->price ?? 0,
                'margin' => $request->margin ?? 0,
                'min_xmr_atomic' => intval($request->min_xmr * 1e12),
                'max_xmr_atomic' => intval($request->max_xmr * 1e12),
                'payment_method_id' => $request->payment_method_id,
                'country' => $request->country,
                'online_or_inperson' => $request->online_or_inperson,
                'terms' => $request->terms,
                'min_trade_time' => $request->min_trade_time,
                'max_trade_time' => $request->max_trade_time,
            ]);

            DB::commit();

            return redirect()->route('offers.show', $offer)
                ->with('success', 'Offer updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update offer', [
                'offer_id' => $offer->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to update offer. Please try again.');
        }
    }

    /**
     * Deactivate an offer
     */
    public function deactivate(Offer $offer)
    {
        $user = Auth::user();
        
        // Check if user owns this offer
        if ($offer->user_id !== $user->id) {
            abort(403, 'Access denied');
        }

        if (!$offer->canBeDeactivated()) {
            return redirect()->back()->with('error', 'This offer cannot be deactivated');
        }

        try {
            $offer->update(['state' => 'inactive']);
            
            return redirect()->back()->with('success', 'Offer deactivated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to deactivate offer', [
                'offer_id' => $offer->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to deactivate offer. Please try again.');
        }
    }

    /**
     * Reactivate an offer
     */
    public function reactivate(Offer $offer)
    {
        $user = Auth::user();
        
        // Check if user owns this offer
        if ($offer->user_id !== $user->id) {
            abort(403, 'Access denied');
        }

        if (!$offer->canBeReactivated()) {
            return redirect()->back()->with('error', 'This offer cannot be reactivated');
        }

        try {
            $offer->update(['state' => 'active']);
            
            return redirect()->back()->with('success', 'Offer reactivated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to reactivate offer', [
                'offer_id' => $offer->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()->with('error', 'Failed to reactivate offer. Please try again.');
        }
    }
}
