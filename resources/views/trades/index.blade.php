@extends('layouts.app')

@section('title', 'My Trades')
@section('description', 'View and manage your Monero trades on Monero Exchange.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">My Trades</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Manage your Monero trades and transactions.
            </p>
        </div>
    </div>

    <!-- Trade Statistics -->
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-4">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Total Trades
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $trades->total() }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Completed
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $trades->where('state', 'completed')->count() }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Active
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $trades->whereIn('state', ['await_deposit', 'escrowed', 'await_payment', 'release_pending'])->count() }}
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300 truncate">
                                Total Volume
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ number_format($trades->where('state', 'completed')->sum('amount_atomic') / 1e12, 4) }} XMR
                            </dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trades List -->
    <div class="mt-8">
        @if($trades->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($trades as $trade)
                        <li>
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            @if($trade->state === 'completed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completed
                                                </span>
                                            @elseif($trade->state === 'escrowed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Escrowed
                                                </span>
                                            @elseif($trade->state === 'await_deposit')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Awaiting Deposit
                                                </span>
                                            @elseif($trade->state === 'await_payment')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    Awaiting Payment
                                                </span>
                                            @elseif($trade->state === 'release_pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    Release Pending
                                                </span>
                                            @elseif($trade->state === 'cancelled')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Cancelled
                                                </span>
                                            @elseif($trade->state === 'refunded')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Refunded
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ number_format($trade->getAmountXmr(), 4) }} XMR
                                                </p>
                                                <p class="ml-2 text-sm text-gray-500 dark:text-gray-300">
                                                    @ {{ number_format($trade->price_per_xmr, 2) }} {{ $trade->currency }}
                                                </p>
                                            </div>
                                            <div class="mt-1">
                                                <p class="text-sm text-gray-500 dark:text-gray-300">
                                                    @if($trade->buyer_id === auth()->id())
                                                        Trading with {{ $trade->seller->username }}
                                                    @else
                                                        Trading with {{ $trade->buyer->username }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('trades.show', $trade) }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                                            View Details
                                        </a>
                                        @if($trade->canBeCancelled())
                                            <form method="POST" action="{{ route('trades.cancel', $trade) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-500 text-sm font-medium">
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Created {{ $trade->created_at->diffForHumans() }}
                                        @if($trade->expires_at)
                                            • Expires {{ $trade->expires_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $trades->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No trades</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                    You haven't made any trades yet.
                </p>
                <div class="mt-6">
                    <a href="{{ route('offers') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Browse Offers
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
