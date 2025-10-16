@extends('layouts.app')

@section('title', 'Leave Feedback')
@section('description', 'Leave feedback for your completed trade.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Leave Feedback
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-300">
                    <p>Share your experience trading with {{ $feedbackTo->username }}.</p>
                </div>

                <!-- Trade Details -->
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Trade Details</h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Trade ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">#{{ $trade->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($trade->getAmountXmr(), 4) }} XMR
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Price</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($trade->price_per_xmr, 2) }} {{ $trade->currency }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Completed</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $trade->updated_at->format('M j, Y g:i A') }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Feedback Form -->
                <form method="POST" action="{{ route('feedback.store', $trade) }}" class="mt-6">
                    @csrf

                    <div class="space-y-6">
                        <!-- Rating -->
                        <div>
                            <label class="text-base font-medium text-gray-900 dark:text-white">Rating</label>
                            <p class="text-sm leading-5 text-gray-500 dark:text-gray-300">
                                How was your trading experience?
                            </p>
                            <fieldset class="mt-4">
                                <div class="space-y-4">
                                    <div class="flex items-center">
                                        <input id="rating-positive" name="rating" type="radio" value="+1" 
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600"
                                               {{ old('rating') === '+1' ? 'checked' : '' }}>
                                        <label for="rating-positive" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <span class="flex items-center">
                                                <svg class="h-5 w-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                Positive (+1)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="rating-neutral" name="rating" type="radio" value="0" 
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600"
                                               {{ old('rating') === '0' ? 'checked' : '' }}>
                                        <label for="rating-neutral" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <span class="flex items-center">
                                                <svg class="h-5 w-5 text-gray-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Neutral (0)
                                            </span>
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="rating-negative" name="rating" type="radio" value="-1" 
                                               class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600"
                                               {{ old('rating') === '-1' ? 'checked' : '' }}>
                                        <label for="rating-negative" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <span class="flex items-center">
                                                <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                                Negative (-1)
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                            @error('rating')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Comment (Optional)
                            </label>
                            <div class="mt-1">
                                <textarea id="comment" name="comment" rows="4" 
                                          class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('comment') border-red-300 @enderror"
                                          placeholder="Share details about your trading experience...">{{ old('comment') }}</textarea>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Maximum 1000 characters. Be honest and constructive in your feedback.
                            </p>
                            @error('comment')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('trades.show', $trade) }}" 
                           class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
