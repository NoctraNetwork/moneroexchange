@extends('layouts.app')

@section('title', 'Create Trade')
@section('description', 'Create a new trade for this Monero offer.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                    Create Trade
                </h3>
                <div class="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-300">
                    <p>Create a new trade for this offer. You will need to send XMR to the escrow address once the trade is created.</p>
                </div>

                <!-- Offer Details -->
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Offer Details</h4>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Seller</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $offer->user->username }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Price</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($offer->getCurrentPrice(), 2) }} {{ $offer->currency }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Min Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($offer->getMinXmr(), 4) }} XMR
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Max Amount</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ number_format($offer->getMaxXmr(), 4) }} XMR
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Payment Method</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $offer->paymentMethod->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-300">Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ ucfirst($offer->side) }} • {{ ucfirst($offer->online_or_inperson) }}
                            </dd>
                        </div>
                    </div>
                </div>

                <!-- Trade Form -->
                <form method="POST" action="{{ route('trades.store', $offer) }}" class="mt-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="amount_xmr" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Amount (XMR)
                            </label>
                            <div class="mt-1">
                                <input type="number" 
                                       name="amount_xmr" 
                                       id="amount_xmr" 
                                       step="0.000000000001" 
                                       min="{{ $offer->getMinXmr() }}" 
                                       max="{{ $offer->getMaxXmr() }}"
                                       value="{{ old('amount_xmr') }}"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('amount_xmr') border-red-300 @enderror"
                                       required>
                            </div>
                            @error('amount_xmr')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Min: {{ number_format($offer->getMinXmr(), 4) }} XMR • 
                                Max: {{ number_format($offer->getMaxXmr(), 4) }} XMR
                            </p>
                        </div>

                        <div>
                            <label for="total_fiat" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total ({{ $offer->currency }})
                            </label>
                            <div class="mt-1">
                                <input type="text" 
                                       id="total_fiat" 
                                       readonly
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-gray-50 dark:bg-gray-600 text-gray-900 dark:text-white sm:text-sm">
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Calculated automatically
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="buyer_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Your Monero Address
                        </label>
                        <div class="mt-1">
                            <input type="text" 
                                   name="buyer_address" 
                                   id="buyer_address" 
                                   value="{{ old('buyer_address') }}"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('buyer_address') border-red-300 @enderror"
                                   placeholder="Enter your Monero address (95 characters)"
                                   required>
                        </div>
                        @error('buyer_address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            This is where your XMR will be sent after the trade is completed.
                        </p>
                    </div>

                    <!-- Trade Terms -->
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Trade Terms</h4>
                        <ul class="text-sm text-gray-500 dark:text-gray-300 space-y-2">
                            <li>• You will send XMR to an escrow address</li>
                            <li>• The seller will confirm payment receipt</li>
                            <li>• You will release escrow to receive your XMR</li>
                            <li>• Trade expires in 24 hours if not completed</li>
                            <li>• A small trading fee will be deducted from the escrow</li>
                        </ul>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('offers.show', $offer) }}" 
                           class="bg-white dark:bg-gray-700 py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Trade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount_xmr');
    const totalInput = document.getElementById('total_fiat');
    const price = {{ $offer->getCurrentPrice() }};
    
    function calculateTotal() {
        const amount = parseFloat(amountInput.value) || 0;
        const total = amount * price;
        totalInput.value = total.toFixed(2);
    }
    
    amountInput.addEventListener('input', calculateTotal);
    calculateTotal(); // Initial calculation
});
</script>
@endsection
