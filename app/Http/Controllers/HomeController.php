<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\User;
use App\Services\PriceIndexService;

class HomeController extends Controller
{
    private PriceIndexService $priceService;

    public function __construct(PriceIndexService $priceService)
    {
        $this->priceService = $priceService;
    }

    /**
     * Show the home page
     */
    public function index()
    {
        $stats = $this->getStats();
        $recentOffers = $this->getRecentOffers();
        $priceData = $this->getPriceData();

        return view('home', compact('stats', 'recentOffers', 'priceData'));
    }

    /**
     * Show offers page
     */
    public function offers(Request $request)
    {
        $query = Offer::with(['user', 'paymentMethod'])
            ->active()
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('side')) {
            $query->where('side', $request->side);
        }

        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method_id', $request->payment_method);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('online_or_inperson')) {
            $query->where('online_or_inperson', $request->online_or_inperson);
        }

        if ($request->filled('min_xmr')) {
            $minAtomic = $request->min_xmr * 1e12;
            $query->where('min_xmr_atomic', '<=', $minAtomic);
        }

        if ($request->filled('max_xmr')) {
            $maxAtomic = $request->max_xmr * 1e12;
            $query->where('max_xmr_atomic', '>=', $maxAtomic);
        }

        $offers = $query->paginate(20);

        $filters = $request->only([
            'side', 'currency', 'payment_method', 'country', 
            'online_or_inperson', 'min_xmr', 'max_xmr'
        ]);

        return view('offers.index', compact('offers', 'filters'));
    }

    /**
     * Show individual offer
     */
    public function showOffer(Offer $offer)
    {
        $offer->load(['user', 'paymentMethod']);
        
        return view('offers.show', compact('offer'));
    }

    /**
     * Show how it works page
     */
    public function howItWorks()
    {
        return view('pages.how-it-works');
    }

    /**
     * Show fees page
     */
    public function fees()
    {
        $fees = $this->getFees();
        return view('pages.fees', compact('fees'));
    }

    /**
     * Show terms page
     */
    public function terms()
    {
        return view('pages.terms');
    }

    /**
     * Show privacy page
     */
    public function privacy()
    {
        return view('pages.privacy');
    }

    /**
     * Show security page
     */
    public function security()
    {
        return view('pages.security');
    }

    /**
     * Get platform statistics
     */
    private function getStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_offers' => Offer::active()->count(),
            'total_trades' => 0, // TODO: Implement when trades are created
            'total_volume' => 0, // TODO: Implement when trades are created
        ];
    }

    /**
     * Get recent offers
     */
    private function getRecentOffers()
    {
        return Offer::with(['user', 'paymentMethod'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get price data
     */
    private function getPriceData(): array
    {
        $currencies = ['USD', 'EUR', 'GBP', 'JPY'];
        $prices = [];

        foreach ($currencies as $currency) {
            $prices[$currency] = $this->priceService->getPrice($currency);
        }

        return $prices;
    }

    /**
     * Get fee information
     */
    private function getFees(): array
    {
        return [
            'trade_fee_bps' => config('monero.trade_fee_bps', 25),
            'withdrawal_fee_bps' => config('monero.withdrawal_fee_bps', 25),
            'min_withdrawal_atomic' => config('monero.min_withdrawal_atomic', 1000000000000),
        ];
    }
}

