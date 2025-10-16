@extends('layouts.app')

@section('title', 'Fees')
@section('description', 'Monero Exchange fee structure - transparent pricing for all trading activities.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Fee Structure</h1>
        <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">Transparent, low-cost fees for all trading activities</p>
    </div>

    <!-- Fee Overview -->
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-12">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-green-900 dark:text-green-100 mb-4">Low Fees, High Value</h2>
            <p class="text-green-800 dark:text-green-200 max-w-3xl mx-auto">
                We believe in fair, transparent pricing. Our fees are among the lowest in the industry, 
                and we only charge when trades are completed successfully.
            </p>
        </div>
    </div>

    <!-- Main Fee Table -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Trading Fees</h2>
        
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fee Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Example</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Trade Completion</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Percentage</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">0.25%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">$0.25 on $100 trade</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">XMR Withdrawal</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Percentage</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">0.25%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">0.0025 XMR on 1 XMR</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">XMR Deposit</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Free</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold text-green-600">0%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">No fee</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Creating Offers</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Free</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold text-green-600">0%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">No fee</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Account Registration</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Free</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold text-green-600">0%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">No fee</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fee Calculator -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Fee Calculator</h2>
        
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Calculate Your Fees</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="trade_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trade Amount (USD)</label>
                            <input type="number" id="trade_amount" step="0.01" min="0" value="100" 
                                   class="mt-1 form-input">
                        </div>
                        <div>
                            <label for="xmr_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">XMR Amount</label>
                            <input type="number" id="xmr_amount" step="0.001" min="0" value="0.67" 
                                   class="mt-1 form-input">
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Fee Breakdown</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Trade Fee (0.25%)</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">$0.25</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Withdrawal Fee (0.25%)</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">0.0017 XMR</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Total Fees</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">$0.25 + 0.0017 XMR</span>
                            </div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <p>You receive: 0.6683 XMR</p>
                            <p>Seller receives: $99.75</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Comparison -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Fee Comparison</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Monero Exchange</h3>
                <div class="text-center mb-4">
                    <span class="text-4xl font-bold text-green-600">0.25%</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400">per trade</p>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <li>✓ Escrow protection</li>
                    <li>✓ No KYC required</li>
                    <li>✓ Multiple payment methods</li>
                    <li>✓ Dispute resolution</li>
                    <li>✓ Reputation system</li>
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 border-2 border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Centralized Exchanges</h3>
                <div class="text-center mb-4">
                    <span class="text-4xl font-bold text-gray-600">0.1% - 0.5%</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400">per trade</p>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <li>✗ KYC required</li>
                    <li>✗ Limited payment methods</li>
                    <li>✗ No escrow protection</li>
                    <li>✗ Account freezes possible</li>
                    <li>✗ Centralized control</li>
                </ul>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Other P2P Platforms</h3>
                <div class="text-center mb-4">
                    <span class="text-4xl font-bold text-yellow-600">0.5% - 2%</span>
                    <p class="text-sm text-gray-500 dark:text-gray-400">per trade</p>
                </div>
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <li>✓ P2P trading</li>
                    <li>✗ Higher fees</li>
                    <li>✗ Limited Monero support</li>
                    <li>✗ Complex verification</li>
                    <li>✗ Limited payment methods</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Payment Method Fees -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Payment Method Information</h2>
        
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Payment Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Speed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fees</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Bank Transfer</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">1-3 days</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Varies by bank</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Most secure, lowest fees</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">PayPal</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Instant</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">2.9% + $0.30</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Friends & family only</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Wise</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">1-2 days</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">0.4% - 0.7%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Good for international</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Cash in Person</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Instant</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">No fees</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Meet in public place</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">Zelle</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Instant</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">Free</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">US only, bank required</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Important Notes -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Important Notes</h2>
        
        <div class="space-y-6">
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Fee Collection</h3>
                <p class="text-yellow-700 dark:text-yellow-300">
                    Fees are automatically deducted from the trade amount. You only pay when trades are completed successfully. 
                    If a trade is cancelled or refunded, no fees are charged.
                </p>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200 mb-2">Payment Method Fees</h3>
                <p class="text-blue-700 dark:text-blue-300">
                    Payment method fees (like PayPal's 2.9%) are separate from our platform fees. 
                    These are charged by the payment provider, not by Monero Exchange.
                </p>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">Transparent Pricing</h3>
                <p class="text-green-700 dark:text-green-300">
                    All fees are clearly displayed before you start a trade. There are no hidden charges or surprise fees. 
                    What you see is what you pay.
                </p>
            </div>
        </div>
    </div>

    <!-- Get Started -->
    <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Ready to Start Trading?</h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">Join thousands of users enjoying low fees and secure trading.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="btn-primary text-lg px-8 py-3">
                Create Account
            </a>
            <a href="{{ route('offers') }}" class="btn-secondary text-lg px-8 py-3">
                Browse Offers
            </a>
        </div>
    </div>
</div>
@endsection

