@extends('layouts.app')

@section('title', 'How It Works')
@section('description', 'Learn how Monero Exchange works - from creating offers to completing trades safely.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">How Monero Exchange Works</h1>
        <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">A simple, secure way to trade Monero with other users worldwide</p>
    </div>

    <!-- Overview -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-12">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-100 mb-4">Peer-to-Peer Trading Made Simple</h2>
            <p class="text-blue-800 dark:text-blue-200 max-w-3xl mx-auto">
                Monero Exchange connects Monero buyers and sellers directly. Our escrow system protects both parties, 
                ensuring safe and secure trades without the need for a centralized exchange.
            </p>
        </div>
    </div>

    <!-- Step by Step Process -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Trading Process</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Step 1 -->
            <div class="text-center">
                <div class="bg-blue-600 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Create Account</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Sign up with a username, password, and PIN. No email required for privacy.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="text-center">
                <div class="bg-blue-600 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Post or Browse Offers</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Create buy/sell offers or browse existing ones. Set your price and payment method.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="text-center">
                <div class="bg-blue-600 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Start Trade</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Initiate a trade. The system creates a unique escrow address for the transaction.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="text-center">
                <div class="bg-blue-600 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-4">4</div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Complete Trade</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Funds are held in escrow until both parties confirm. Then released automatically.
                </p>
            </div>
        </div>
    </div>

    <!-- Detailed Process -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Detailed Trading Flow</h2>
        
        <div class="space-y-8">
            <!-- For Sellers -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">For Sellers (Selling XMR)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">1. Create Sell Offer</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Set your price per XMR</li>
                            <li>• Choose payment method (Bank, PayPal, Cash, etc.)</li>
                            <li>• Specify amount range (min/max XMR)</li>
                            <li>• Add terms and conditions</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">2. Wait for Buyer</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Buyers can find your offer in the listings</li>
                            <li>• They can contact you with questions</li>
                            <li>• When ready, they'll start a trade</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">3. Deposit XMR to Escrow</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• System generates unique subaddress for this trade</li>
                            <li>• Send exact XMR amount to this address</li>
                            <li>• Wait for confirmations (usually 10 blocks)</li>
                            <li>• Funds are locked in escrow automatically</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">4. Confirm Payment & Release</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Buyer sends payment to you</li>
                            <li>• Confirm you received the payment</li>
                            <li>• Enter your PIN to release XMR to buyer</li>
                            <li>• Trade completed, leave feedback</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- For Buyers -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">For Buyers (Buying XMR)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">1. Find Sell Offer</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Browse offers or use filters</li>
                            <li>• Check trader reputation and terms</li>
                            <li>• Contact trader if you have questions</li>
                            <li>• Click "Start Trade" when ready</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">2. Wait for Escrow</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Seller deposits XMR to escrow address</li>
                            <li>• System confirms deposit and confirmations</li>
                            <li>• XMR is locked in escrow for your protection</li>
                            <li>• You'll be notified when ready</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">3. Send Payment</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Send payment using agreed method</li>
                            <li>• Include trade reference if required</li>
                            <li>• Upload payment proof if needed</li>
                            <li>• Wait for seller confirmation</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">4. Receive XMR</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-1">
                            <li>• Seller confirms payment received</li>
                            <li>• XMR is released from escrow to you</li>
                            <li>• Check your Monero wallet</li>
                            <li>• Leave feedback for the seller</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Features -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Security Features</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-green-100 dark:bg-green-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Escrow Protection</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Funds are held in secure escrow until both parties confirm. No risk of losing your money.
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">PIN Security</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Sensitive actions require your PIN. Multiple failed attempts result in temporary lockouts.
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-purple-100 dark:bg-purple-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">PGP Encryption</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Optional PGP keys for encrypted communication and enhanced security.
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-yellow-100 dark:bg-yellow-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Reputation System</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        User feedback and completion rates help you choose trustworthy trading partners.
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-red-100 dark:bg-red-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Dispute Resolution</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        If issues arise, moderators can help resolve disputes and make fair decisions.
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-indigo-100 dark:bg-indigo-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No JavaScript</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Platform works without JavaScript for maximum security and privacy protection.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Frequently Asked Questions</h2>
        
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Is it safe to trade on Monero Exchange?</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Yes! Our escrow system protects both buyers and sellers. Funds are held securely until both parties confirm the trade is complete. We also have a reputation system and dispute resolution process.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">What payment methods are supported?</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    We support many payment methods including bank transfers, PayPal, Wise, Revolut, Zelle, Venmo, Cash App, Apple Pay, Google Pay, and cash in-person meetings.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">How long do trades take?</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Most trades complete within 1-24 hours depending on the payment method. Bank transfers may take longer, while digital payments are usually instant.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">What are the fees?</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    We charge a small fee (0.25%) on completed trades. This helps maintain the platform and escrow system. There are no fees for creating offers or browsing.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Do I need to provide personal information?</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    No! We only require a username, password, and PIN. No email address or personal information is required. You can optionally add a country and PGP key for enhanced security.
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">What if something goes wrong?</h3>
                <p class="text-gray-600 dark:text-gray-400">
                    If you have any issues, you can escalate the trade to dispute resolution. Our moderators will review the case and make a fair decision based on the evidence provided.
                </p>
            </div>
        </div>
    </div>

    <!-- Get Started -->
    <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Ready to Start Trading?</h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">Join thousands of users trading Monero safely and securely.</p>
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

