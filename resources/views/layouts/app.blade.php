<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Monero Exchange') - Monero Exchange</title>
    <meta name="description" content="@yield('description', 'Peer-to-peer Monero exchange with escrow protection. Trade XMR safely with other users worldwide.')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Styles -->
    <link href="{{ asset('assets/app.css') }}" rel="stylesheet">
    
    <!-- Security Scripts -->
    <script src="{{ asset('js/csrf-protection.js') }}" defer></script>
    
    @stack('styles')
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="min-h-full">
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="text-xl font-bold text-gray-900 dark:text-white">
                                Monero Exchange
                            </a>
                        </div>
                        
                        <!-- Navigation Links -->
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('home') }}" class="border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Home
                            </a>
                            <a href="{{ route('offers') }}" class="border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Offers
                            </a>
                            <a href="{{ route('how-it-works') }}" class="border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                How It Works
                            </a>
                            <a href="{{ route('fees') }}" class="border-transparent text-gray-500 dark:text-gray-300 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Fees
                            </a>
                        </div>
                    </div>
                    
                    <!-- Right side -->
                    <div class="flex items-center">
                        @auth
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('dashboard') }}" class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200">
                                    Dashboard
                                </a>
                                <a href="{{ route('trades.index') }}" class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200">
                                    My Trades
                                </a>
                                <a href="{{ route('feedback.index') }}" class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200">
                                    Feedback
                                </a>
                                <a href="{{ route('offers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Post Offer
                                </a>
                                <form method="POST" action="{{ route('logout') }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('login') }}" class="text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200">
                                    Login
                                </a>
                                <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    Register
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Exchange</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="{{ route('offers') }}" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Browse Offers</a></li>
                            <li><a href="{{ route('how-it-works') }}" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">How It Works</a></li>
                            <li><a href="{{ route('fees') }}" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Fees</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Legal</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="{{ route('terms') }}" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Terms of Service</a></li>
                            <li><a href="{{ route('privacy') }}" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Privacy Policy</a></li>
                            <li><a href="{{ route('security') }}" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Security</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Support</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Help Center</a></li>
                            <li><a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Contact Us</a></li>
                            <li><a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">Status</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">About</h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">About Monero Exchange</a></li>
                            <li><a href="#" class="text-base text-gray-500 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">API Documentation</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                    <p class="text-base text-gray-400 text-center">
                        &copy; {{ date('Y') }} Monero Exchange. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>

