@extends('layouts.app')

@section('title', 'Feedback Given')
@section('description', 'View feedback you have given to other users.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Feedback Given</h1>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                Feedback you have given to other users.
            </p>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="mt-8">
        @if($feedback->count() > 0)
            <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($feedback as $item)
                        <li>
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            @if($item->isPositive())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Positive
                                                </span>
                                            @elseif($item->isNeutral())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    Neutral
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                    Negative
                                                </span>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="flex items-center">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    <a href="{{ route('feedback.profile', $item->toUser) }}" class="text-blue-600 hover:text-blue-500">
                                                        {{ $item->toUser->username }}
                                                    </a>
                                                </p>
                                                <p class="ml-2 text-sm text-gray-500 dark:text-gray-300">
                                                    Trade #{{ $item->trade->id }}
                                                </p>
                                            </div>
                                            @if($item->comment)
                                                <div class="mt-1">
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                                        "{{ $item->comment }}"
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right text-sm text-gray-500 dark:text-gray-300">
                                        {{ $item->created_at->format('M j, Y') }}
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $feedback->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No feedback given yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                    You haven't given any feedback yet. Complete some trades to start leaving feedback.
                </p>
                <div class="mt-6">
                    <a href="{{ route('trades.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        View My Trades
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Navigation -->
    <div class="mt-8 flex justify-center space-x-4">
        <a href="{{ route('feedback.index') }}" class="text-blue-600 hover:text-blue-500 text-sm font-medium">
            ← View Feedback Received
        </a>
    </div>
</div>
@endsection
