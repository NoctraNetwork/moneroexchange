@extends('layouts.app')

@section('title', 'RPC Configuration')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl sm:truncate">
                    RPC Configuration
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300">
                    Configure Monero RPC and Wallet RPC settings
                </p>
            </div>
        </div>

        <!-- Configuration Form -->
        <div class="mt-8">
            <form action="{{ route('admin.rpc-config.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Monero RPC Settings -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Monero RPC (monerod) Settings
                        </h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="monero_rpc_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Host
                                </label>
                                <input type="text" name="monero_rpc_host" id="monero_rpc_host" 
                                       value="{{ $settings['monero_rpc_host'] ?? 'localhost' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="monero_rpc_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Port
                                </label>
                                <input type="number" name="monero_rpc_port" id="monero_rpc_port" 
                                       value="{{ $settings['monero_rpc_port'] ?? '18081' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="monero_rpc_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Username
                                </label>
                                <input type="text" name="monero_rpc_username" id="monero_rpc_username" 
                                       value="{{ $settings['monero_rpc_username'] ?? '' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="monero_rpc_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Password
                                </label>
                                <input type="password" name="monero_rpc_password" id="monero_rpc_password" 
                                       value="{{ $settings['monero_rpc_password'] ?? '' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="monero_rpc_ssl" id="monero_rpc_ssl" value="1"
                                           {{ ($settings['monero_rpc_ssl'] ?? false) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded">
                                    <label for="monero_rpc_ssl" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                        Use SSL/TLS
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wallet RPC Settings -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Wallet RPC (monero-wallet-rpc) Settings
                        </h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="monero_wallet_rpc_host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Host
                                </label>
                                <input type="text" name="monero_wallet_rpc_host" id="monero_wallet_rpc_host" 
                                       value="{{ $settings['monero_wallet_rpc_host'] ?? 'localhost' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="monero_wallet_rpc_port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Port
                                </label>
                                <input type="number" name="monero_wallet_rpc_port" id="monero_wallet_rpc_port" 
                                       value="{{ $settings['monero_wallet_rpc_port'] ?? '18083' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="monero_wallet_rpc_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Username
                                </label>
                                <input type="text" name="monero_wallet_rpc_username" id="monero_wallet_rpc_username" 
                                       value="{{ $settings['monero_wallet_rpc_username'] ?? '' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="monero_wallet_rpc_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Password
                                </label>
                                <input type="password" name="monero_wallet_rpc_password" id="monero_wallet_rpc_password" 
                                       value="{{ $settings['monero_wallet_rpc_password'] ?? '' }}"
                                       class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <div class="flex items-center">
                                    <input type="checkbox" name="monero_wallet_rpc_ssl" id="monero_wallet_rpc_ssl" value="1"
                                           {{ ($settings['monero_wallet_rpc_ssl'] ?? false) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded">
                                    <label for="monero_wallet_rpc_ssl" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                                        Use SSL/TLS
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Connection -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                            Test Connection
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-300 mb-4">
                            Test the RPC connection to ensure it's working properly.
                        </p>
                        <button type="button" id="test-connection" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Test Connection
                        </button>
                        <div id="test-result" class="mt-4 hidden">
                            <div class="rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                            Connection Successful
                                        </h3>
                                        <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                                            <p id="test-message">RPC connection is working properly.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('test-connection').addEventListener('click', function() {
    const button = this;
    const resultDiv = document.getElementById('test-result');
    const messageDiv = document.getElementById('test-message');
    
    button.disabled = true;
    button.textContent = 'Testing...';
    
    fetch('{{ route("admin.rpc-config.test") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.classList.remove('hidden');
        if (data.success) {
            resultDiv.querySelector('.rounded-md').className = 'rounded-md p-4 bg-green-50 dark:bg-green-900';
            resultDiv.querySelector('h3').className = 'text-sm font-medium text-green-800 dark:text-green-200';
            resultDiv.querySelector('div.mt-2').className = 'mt-2 text-sm text-green-700 dark:text-green-300';
            messageDiv.textContent = data.message;
        } else {
            resultDiv.querySelector('.rounded-md').className = 'rounded-md p-4 bg-red-50 dark:bg-red-900';
            resultDiv.querySelector('h3').className = 'text-sm font-medium text-red-800 dark:text-red-200';
            resultDiv.querySelector('div.mt-2').className = 'mt-2 text-sm text-red-700 dark:text-red-300';
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        resultDiv.classList.remove('hidden');
        resultDiv.querySelector('.rounded-md').className = 'rounded-md p-4 bg-red-50 dark:bg-red-900';
        resultDiv.querySelector('h3').className = 'text-sm font-medium text-red-800 dark:text-red-200';
        resultDiv.querySelector('div.mt-2').className = 'mt-2 text-sm text-red-700 dark:text-red-300';
        messageDiv.textContent = 'Connection test failed: ' + error.message;
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Test Connection';
    });
});
</script>
@endsection

