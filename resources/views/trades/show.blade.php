@extends('layouts.app')

@section('title', 'Trade Details')
@section('description', 'View and manage your trade details.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="max-w-4xl mx-auto">
        <!-- Trade Header -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                            Trade #{{ $trade->id }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                            {{ number_format($trade->getAmountXmr(), 4) }} XMR @ {{ number_format($trade->price_per_xmr, 2) }} {{ $trade->currency }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
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
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Trade Details -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                        Trade Details
                    </h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($trade->getAmountXmr(), 4) }} XMR
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Price per XMR</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($trade->price_per_xmr, 2) }} {{ $trade->currency }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($trade->getTotalFiatAmount(), 2) }} {{ $trade->currency }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $trade->created_at->format('M j, Y g:i A') }}
                            </dd>
                        </div>
                        @if($trade->expires_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Expires</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $trade->expires_at->format('M j, Y g:i A') }}
                                @if($trade->expires_at->isFuture())
                                    <span class="text-yellow-600">({{ $trade->expires_at->diffForHumans() }})</span>
                                @else
                                    <span class="text-red-600">(Expired)</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Participants -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                        Participants
                    </h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Buyer</dt>
                            <dd class="mt-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <a href="{{ route('feedback.profile', $trade->buyer) }}" 
                                           class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                            {{ $trade->buyer->username }}
                                        </a>
                                        @if($trade->buyer_id === auth()->id())
                                            <span class="text-blue-600 ml-1">(You)</span>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($trade->buyer->getReputationScore(), 1) }}%
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">
                                            Reputation
                                        </div>
                                    </div>
                                </div>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Seller</dt>
                            <dd class="mt-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <a href="{{ route('feedback.profile', $trade->seller) }}" 
                                           class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                            {{ $trade->seller->username }}
                                        </a>
                                        @if($trade->seller_id === auth()->id())
                                            <span class="text-blue-600 ml-1">(You)</span>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ number_format($trade->seller->getReputationScore(), 1) }}%
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-300">
                                            Reputation
                                        </div>
                                    </div>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Escrow Information -->
        @if($trade->escrow_subaddr)
        <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Escrow Information
                </h3>
                
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Escrow Address</dt>
                        <dd class="mt-1">
                            <div class="flex items-center">
                                <input type="text" 
                                       value="{{ $trade->escrow_subaddr }}" 
                                       readonly
                                       class="flex-1 appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-gray-50 dark:bg-gray-600 text-gray-900 dark:text-white text-sm font-mono">
                                <button onclick="copyToClipboard('{{ $trade->escrow_subaddr }}')" 
                                        class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Copy
                                </button>
                            </div>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Escrow Balance</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ number_format($escrowStatus['balance_xmr'], 4) }} XMR
                            @if($escrowStatus['has_sufficient_funds'])
                                <span class="text-green-600">✓</span>
                            @else
                                <span class="text-yellow-600">⚠</span>
                            @endif
                        </dd>
                    </div>
                </div>

                @if($trade->isAwaitingDeposit())
                <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Awaiting XMR Deposit
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>Please send exactly <strong>{{ number_format($trade->getAmountXmr(), 4) }} XMR</strong> to the escrow address above. The trade will automatically proceed once the deposit is confirmed.</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Actions
                </h3>
                
                <div class="flex flex-wrap gap-3">
                    @if($trade->buyer_id === auth()->id())
                        @if($trade->canBeReleased())
                            <form method="POST" action="{{ route('trades.release', $trade) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Release Escrow
                                </button>
                            </form>
                        @endif
                    @elseif($trade->seller_id === auth()->id())
                        @if($trade->state === 'escrowed')
                            <form method="POST" action="{{ route('trades.confirm-payment', $trade) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Confirm Payment Received
                                </button>
                            </form>
                        @endif
                        
                        @if($trade->canBeRefunded())
                            <form method="POST" action="{{ route('trades.refund', $trade) }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Refund Escrow
                                </button>
                            </form>
                        @endif
                    @endif
                    
                    @if($trade->canBeCancelled())
                        <form method="POST" action="{{ route('trades.cancel', $trade) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel Trade
                            </button>
                        </form>
                    @endif

                    @if($trade->isCompleted())
                        @php
                            $existingFeedback = \App\Models\Feedback::where('trade_id', $trade->id)
                                ->where('from_user_id', auth()->id())
                                ->first();
                        @endphp
                        
                        @if(!$existingFeedback)
                            <a href="{{ route('feedback.create', $trade) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                Leave Feedback
                            </a>
                        @else
                            <span class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-600">
                                Feedback Given
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Trade Events -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Trade History
                </h3>
                
                <div class="flow-root">
                    <ul class="-mb-8">
                        @foreach($trade->events()->orderBy('created_at')->get() as $event)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                                {{ ucwords(str_replace('_', ' ', $event->type)) }}
                                            </p>
                                            @if($event->data_json)
                                            <div class="mt-1 text-sm text-gray-900 dark:text-white">
                                                @if(isset($event->data_json['amount_xmr']))
                                                    Amount: {{ number_format($event->data_json['amount_xmr'], 4) }} XMR
                                                @endif
                                                @if(isset($event->data_json['tx_hash']))
                                                    <br>TX: <span class="font-mono text-xs">{{ substr($event->data_json['tx_hash'], 0, 16) }}...</span>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        <div class="text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-300">
                                            {{ $event->created_at->format('M j, g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // You could show a toast notification here
        console.log('Address copied to clipboard');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
@endsection
