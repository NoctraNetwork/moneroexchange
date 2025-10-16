@extends('layouts.app')

@section('title', $user->username . ' - User Profile')
@section('description', 'View ' . $user->username . '\'s trading profile and feedback.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="max-w-4xl mx-auto">
        <!-- User Profile Header -->
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-2xl font-bold text-white">
                                    {{ strtoupper(substr($user->username, 0, 1)) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $user->username }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                Member since {{ $user->created_at->format('M Y') }}
                                ({{ $stats['account_age_days'] }} days)
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['reputation_score'], 1) }}%
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-300">
                            Reputation Score
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Trading Statistics -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                        Trading Statistics
                    </h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total Trades</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $tradingStats['total_trades'] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Completed Trades</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $tradingStats['completed_trades'] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Completion Rate</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($stats['completion_rate'], 1) }}%
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total Volume</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($tradingStats['total_volume_xmr'], 4) }} XMR
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Feedback Statistics -->
            <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                        Feedback Statistics
                    </h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Total Feedback</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $stats['total_feedback'] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Positive</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $stats['positive_count'] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Neutral</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $stats['neutral_count'] }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Negative</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $stats['negative_count'] }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Reputation Score Visualization -->
        <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Reputation Score
                </h3>
                <div class="flex items-center">
                    <div class="flex-1">
                        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                            <div class="bg-blue-600 h-4 rounded-full" 
                                 style="width: {{ max(0, min(100, $stats['reputation_score'])) }}%"></div>
                        </div>
                    </div>
                    <div class="ml-4">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ number_format($stats['reputation_score'], 1) }}%
                        </span>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-300">
                    Based on {{ $stats['total_feedback'] }} feedback entries
                </p>
            </div>
        </div>

        <!-- Recent Feedback -->
        @if($recentFeedback->count() > 0)
        <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Recent Feedback
                </h3>
                
                <div class="space-y-4">
                    @foreach($recentFeedback as $item)
                        <div class="border-l-4 border-gray-200 dark:border-gray-700 pl-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($item->isPositive())
                                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        @elseif($item->isNeutral())
                                            <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $item->fromUser->username }}
                                        </p>
                                        @if($item->comment)
                                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                                "{{ $item->comment }}"
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-right text-sm text-gray-500 dark:text-gray-300">
                                    {{ $item->created_at->format('M j, Y') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($stats['total_feedback'] > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('feedback.profile', $user) }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
                            View All Feedback ({{ $stats['total_feedback'] }})
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- User Actions -->
        @if(auth()->id() !== $user->id)
        <div class="mt-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Actions
                </h3>
                <div class="flex space-x-4">
                    <a href="{{ route('offers') }}?user={{ $user->id }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View {{ $user->username }}'s Offers
                    </a>
                    <a href="{{ route('trades.create', ['offer' => 'any']) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Start a Trade
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
