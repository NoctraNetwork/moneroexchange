@extends('layouts.app')

@section('title', 'Offer Details')
@section('description', 'View detailed information about this Monero trading offer.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('home') }}" class="hover:text-gray-700 dark:hover:text-gray-300">Home</a></li>
            <li><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg></li>
            <li><a href="{{ route('offers') }}" class="hover:text-gray-700 dark:hover:text-gray-300">Offers</a></li>
            <li><svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg></li>
            <li class="text-gray-900 dark:text-white">Offer Details</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Offer Details -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4">
                            <span class="badge badge-offer-sell">Sell</span>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Selling XMR for USD</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400">by <span class="font-medium">alice</span></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-gray-900 dark:text-white">$150.00</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">per XMR</p>
                        </div>
                    </div>

                    <!-- Price Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Amount Range</h3>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">0.001 - 0.01 XMR</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">$0.15 - $1.50 total</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Method</h3>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">Bank Transfer</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Online</p>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Terms and Conditions</h3>
                        <div class="prose dark:prose-invert max-w-none">
                            <p>Bank transfer only. Must be from a US bank account. I will release XMR once payment is confirmed.</p>
                            
                            <h4>Payment Instructions:</h4>
                            <ul>
                                <li>Send payment to the bank account provided after trade starts</li>
                                <li>Include the trade reference number in the payment memo</li>
                                <li>Payment must be sent within 24 hours of trade initiation</li>
                                <li>I will release XMR within 1 hour of payment confirmation</li>
                            </ul>

                            <h4>Important Notes:</h4>
                            <ul>
                                <li>No chargebacks or disputes after XMR release</li>
                                <li>I reserve the right to cancel if payment is not received on time</li>
                                <li>Contact me immediately if you have any issues</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-4">
                        <a href="#" class="btn-primary flex-1 text-center">
                            Start Trade
                        </a>
                        <button class="btn-secondary">
                            Contact Trader
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trader Information -->
            <div class="mt-8 bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Trader Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Username</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">alice</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Member Since</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">January 2024</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Reputation</h4>
                            <div class="flex items-center">
                                <span class="text-lg font-semibold text-green-600">98%</span>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(12 trades)</span>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Completion Rate</h4>
                            <div class="flex items-center">
                                <span class="text-lg font-semibold text-green-600">100%</span>
                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">(12/12)</span>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Location</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">United States</p>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">PGP Key</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Verified ✓</p>
                        </div>
                    </div>

                    <!-- Recent Feedback -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Recent Feedback</h4>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-shrink-0">
                                    <span class="badge badge-success">+1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white">"Fast and reliable trader. XMR was released quickly after payment confirmation."</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">by trader123 • 2 days ago</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div class="flex-shrink-0">
                                    <span class="badge badge-success">+1</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 dark:text-white">"Great communication and smooth transaction. Highly recommended!"</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">by crypto_buyer • 1 week ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Price Calculator -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Price Calculator</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="xmr_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">XMR Amount</label>
                            <input type="number" id="xmr_amount" step="0.001" min="0.001" max="0.01" value="0.005" 
                                   class="mt-1 form-input">
                        </div>
                        <div>
                            <label for="usd_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">USD Amount</label>
                            <input type="number" id="usd_amount" step="0.01" min="0.15" max="1.50" value="0.75" 
                                   class="mt-1 form-input" readonly>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            <p>Rate: $150.00 per XMR</p>
                            <p>Min: 0.001 XMR ($0.15)</p>
                            <p>Max: 0.01 XMR ($1.50)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Safety Tips -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200 mb-3">Safety Tips</h3>
                    <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-2">
                        <li>• Always verify the trader's reputation</li>
                        <li>• Never send payment outside the platform</li>
                        <li>• Keep all communication in the trade chat</li>
                        <li>• Report any suspicious behavior immediately</li>
                        <li>• Use the escrow system for protection</li>
                    </ul>
                </div>
            </div>

            <!-- Related Offers -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Related Offers</h3>
                    <div class="space-y-3">
                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Sell XMR</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Bank Transfer • $149.00</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">$149.00</span>
                            </div>
                        </div>
                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Sell XMR</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">PayPal • $151.00</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">$151.00</span>
                            </div>
                        </div>
                        <div class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">Buy XMR</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Bank Transfer • $148.00</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">$148.00</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('offers') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                            View all offers →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

