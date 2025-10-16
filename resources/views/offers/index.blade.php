@extends('layouts.app')

@section('title', 'Browse Offers')
@section('description', 'Browse buy and sell offers for Monero (XMR) from traders worldwide.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Browse Offers</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Find the best Monero trading opportunities from verified traders.</p>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filters</h3>
            <form method="GET" action="{{ route('offers') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="side" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Side</label>
                    <select name="side" id="side" class="mt-1 form-input">
                        <option value="">All</option>
                        <option value="buy" {{ request('side') === 'buy' ? 'selected' : '' }}>Buy XMR</option>
                        <option value="sell" {{ request('side') === 'sell' ? 'selected' : '' }}>Sell XMR</option>
                    </select>
                </div>

                <div>
                    <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
                    <select name="currency" id="currency" class="mt-1 form-input">
                        <option value="">All</option>
                        <option value="USD" {{ request('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ request('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                        <option value="GBP" {{ request('currency') === 'GBP' ? 'selected' : '' }}>GBP</option>
                        <option value="JPY" {{ request('currency') === 'JPY' ? 'selected' : '' }}>JPY</option>
                    </select>
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="mt-1 form-input">
                        <option value="">All</option>
                        <option value="1" {{ request('payment_method') === '1' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="2" {{ request('payment_method') === '2' ? 'selected' : '' }}>PayPal</option>
                        <option value="3" {{ request('payment_method') === '3' ? 'selected' : '' }}>Cash in Person</option>
                        <option value="4" {{ request('payment_method') === '4' ? 'selected' : '' }}>Wise</option>
                    </select>
                </div>

                <div>
                    <label for="online_or_inperson" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                    <select name="online_or_inperson" id="online_or_inperson" class="mt-1 form-input">
                        <option value="">All</option>
                        <option value="online" {{ request('online_or_inperson') === 'online' ? 'selected' : '' }}>Online</option>
                        <option value="inperson" {{ request('online_or_inperson') === 'inperson' ? 'selected' : '' }}>In-Person</option>
                    </select>
                </div>

                <div>
                    <label for="min_xmr" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Min XMR</label>
                    <input type="number" name="min_xmr" id="min_xmr" step="0.001" min="0" 
                           value="{{ request('min_xmr') }}" class="mt-1 form-input">
                </div>

                <div>
                    <label for="max_xmr" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max XMR</label>
                    <input type="number" name="max_xmr" id="max_xmr" step="0.001" min="0" 
                           value="{{ request('max_xmr') }}" class="mt-1 form-input">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full">
                        Apply Filters
                    </button>
                </div>

                <div class="flex items-end">
                    <a href="{{ route('offers') }}" class="btn-secondary w-full text-center">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Offers List -->
    <div class="space-y-6">
        <!-- Sample Offer 1 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <span class="badge badge-offer-sell">Sell</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Selling XMR for USD</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">by <span class="font-medium">alice</span> • Bank Transfer • Online</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">$150.00</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">per XMR</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Amount Range</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">0.001 - 0.01 XMR</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Bank Transfer</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Reputation</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">98% (12 trades)</p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Bank transfer only. Must be from a US bank account. I will release XMR once payment is confirmed.
                    </p>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Posted 2 hours ago</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <a href="{{ route('offers.show', 1) }}" class="btn-primary">
                        View Offer
                    </a>
                </div>
            </div>
        </div>

        <!-- Sample Offer 2 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <span class="badge badge-offer-buy">Buy</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Buying XMR with EUR</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">by <span class="font-medium">bob</span> • PayPal • Online</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">€140.00</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">per XMR</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Amount Range</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">0.005 - 0.05 XMR</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">PayPal</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Reputation</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">95% (8 trades)</p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        PayPal friends and family only. No business payments. I will send XMR once payment is received.
                    </p>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Posted 4 hours ago</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <a href="{{ route('offers.show', 2) }}" class="btn-primary">
                        View Offer
                    </a>
                </div>
            </div>
        </div>

        <!-- Sample Offer 3 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <span class="badge badge-offer-sell">Sell</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Selling XMR for GBP</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">by <span class="font-medium">charlie</span> • Cash • In-Person</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">£120.00</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">per XMR</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Amount Range</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">0.002 - 0.02 XMR</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Cash in Person</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Reputation</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">100% (5 trades)</p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Cash only. Meet in central London. Bring ID. I will transfer XMR once cash is exchanged.
                    </p>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Posted 1 day ago</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <a href="{{ route('offers.show', 3) }}" class="btn-primary">
                        View Offer
                    </a>
                </div>
            </div>
        </div>

        <!-- Sample Offer 4 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <span class="badge badge-offer-buy">Buy</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Buying XMR with JPY</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">by <span class="font-medium">david</span> • Wise • Online</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">¥22,000</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">per XMR</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Amount Range</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">0.01 - 0.1 XMR</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Wise</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Reputation</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">92% (3 trades)</p>
                    </div>
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Wise transfer only. I will send XMR once payment is confirmed. Fast and reliable.
                    </p>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Posted 6 hours ago</span>
                        <span class="badge badge-success">Active</span>
                    </div>
                    <a href="{{ route('offers.show', 4) }}" class="btn-primary">
                        View Offer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex items-center justify-between">
        <div class="text-sm text-gray-700 dark:text-gray-300">
            Showing <span class="font-medium">1</span> to <span class="font-medium">4</span> of <span class="font-medium">4</span> results
        </div>
        <div class="flex space-x-2">
            <button disabled class="px-3 py-2 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md cursor-not-allowed">
                Previous
            </button>
            <button class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md">
                1
            </button>
            <button disabled class="px-3 py-2 text-sm font-medium text-gray-500 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md cursor-not-allowed">
                Next
            </button>
        </div>
    </div>
</div>
@endsection

