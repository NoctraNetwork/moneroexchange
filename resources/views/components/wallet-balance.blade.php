@props(['user', 'showDetails' => true])

@php
    $walletBalanceService = app(\App\Services\WalletBalanceService::class);
    $balanceStats = $walletBalanceService->getBalanceStats($user);
@endphp

<div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
            Wallet Balance
        </h3>
        
        @if($user->wallet_address)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total Balance</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ number_format($balanceStats['total_balance'], 4) }} XMR
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Available</dt>
                    <dd class="mt-1 text-2xl font-semibold text-green-600">
                        {{ number_format($balanceStats['available_balance'], 4) }} XMR
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Locked in Trades</dt>
                    <dd class="mt-1 text-2xl font-semibold text-orange-600">
                        {{ number_format($balanceStats['locked_balance'], 4) }} XMR
                    </dd>
                </div>
            </div>

            @if($showDetails)
                <div class="mt-6">
                    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-300">
                        <span>Wallet Address:</span>
                        <span class="font-mono text-xs break-all">{{ $user->wallet_address }}</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-sm text-gray-500 dark:text-gray-300">
                        <span>Last Updated:</span>
                        <span>{{ $balanceStats['last_updated']->format('M j, Y g:i A') }}</span>
                    </div>
                </div>
            @endif

            @if($balanceStats['available_balance'] == 0 && $balanceStats['total_balance'] > 0)
                <div class="mt-4 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                All funds are locked in active trades
                            </h3>
                            <div class="mt-2 text-sm text-orange-700 dark:text-orange-300">
                                <p>You cannot create new sell offers until your active trades are completed.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($balanceStats['total_balance'] == 0)
                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                No XMR in wallet
                            </h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                <p>You need to deposit XMR to your wallet before creating sell offers.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No wallet address</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                    You need to set up a wallet address to create sell offers.
                </p>
                <div class="mt-6">
                    <a href="{{ route('settings') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Setup Wallet
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
