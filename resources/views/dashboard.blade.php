@extends('layouts.app')

@section('title', 'Dashboard')
@section('description', 'Your Monero Exchange dashboard - manage your trades, offers, and account.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Welcome back, {{ auth()->user()->username }}!</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Manage your trades, offers, and account settings.</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Active Offers
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                3
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Completed Trades
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                12
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Pending Trades
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                2
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Reputation
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                98%
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('offers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-md text-center font-medium">
                Post New Offer
            </a>
            <a href="{{ route('offers') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-md text-center font-medium">
                Browse Offers
            </a>
            <a href="#" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-md text-center font-medium">
                My Trades
            </a>
            <a href="#" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-md text-center font-medium">
                Settings
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Trades -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Recent Trades</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center">
                            <span class="badge badge-trade-escrowed">Escrowed</span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Selling 0.5 XMR</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">with trader123</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">$75.00</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">2 hours ago</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center">
                            <span class="badge badge-trade-completed">Completed</span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Buying 0.2 XMR</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">with alice</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">$30.00</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">1 day ago</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center">
                            <span class="badge badge-trade-await-payment">Awaiting Payment</span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Selling 1.0 XMR</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">with bob</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">$150.00</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">3 days ago</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="#" class="text-blue-600 hover:text-blue-500 text-sm font-medium">View all trades →</a>
                </div>
            </div>
        </div>

        <!-- My Offers -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">My Offers</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center">
                            <span class="badge badge-offer-sell">Sell</span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">0.1 - 1.0 XMR</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Bank Transfer • $150.00</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center">
                            <span class="badge badge-offer-buy">Buy</span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">0.5 - 5.0 XMR</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">PayPal • $145.00</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-success">Active</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                        <div class="flex items-center">
                            <span class="badge badge-offer-sell">Sell</span>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">0.2 - 2.0 XMR</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Cash • $148.00</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-warning">Paused</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="#" class="text-blue-600 hover:text-blue-500 text-sm font-medium">Manage offers →</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Status -->
    <div class="mt-8 bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Security Status</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">PIN Set</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">4-digit PIN configured</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">PGP Key</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Not configured</p>
                    </div>
                </div>

                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">2FA Enabled</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">PIN + Password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

