@extends('layouts.app')

@section('title', 'Register')
@section('description', 'Create a new Monero Exchange account to start trading Monero safely.')

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Create your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Or
            <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                sign in to your existing account
            </a>
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form class="space-y-6" method="POST" action="{{ route('register') }}">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Username
                    </label>
                    <div class="mt-1">
                        <input id="username" name="username" type="text" autocomplete="username" required
                               value="{{ old('username') }}"
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('username') border-red-300 @enderror">
                    </div>
                    @error('username')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Password
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password') border-red-300 @enderror">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Confirm Password
                    </label>
                    <div class="mt-1">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password_confirmation') border-red-300 @enderror">
                    </div>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        PIN (4-8 digits)
                    </label>
                    <div class="mt-1">
                        <input id="pin" name="pin" type="password" autocomplete="off" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('pin') border-red-300 @enderror">
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Your PIN is used for sensitive actions like releasing escrow funds.
                    </p>
                    @error('pin')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pin_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Confirm PIN
                    </label>
                    <div class="mt-1">
                        <input id="pin_confirmation" name="pin_confirmation" type="password" autocomplete="off" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('pin_confirmation') border-red-300 @enderror">
                    </div>
                    @error('pin_confirmation')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Country (Optional)
                    </label>
                    <div class="mt-1">
                        <select id="country" name="country"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('country') border-red-300 @enderror">
                            <option value="">Select Country</option>
                            <option value="US" {{ old('country') === 'US' ? 'selected' : '' }}>United States</option>
                            <option value="CA" {{ old('country') === 'CA' ? 'selected' : '' }}>Canada</option>
                            <option value="GB" {{ old('country') === 'GB' ? 'selected' : '' }}>United Kingdom</option>
                            <option value="DE" {{ old('country') === 'DE' ? 'selected' : '' }}>Germany</option>
                            <option value="FR" {{ old('country') === 'FR' ? 'selected' : '' }}>France</option>
                            <option value="AU" {{ old('country') === 'AU' ? 'selected' : '' }}>Australia</option>
                            <option value="JP" {{ old('country') === 'JP' ? 'selected' : '' }}>Japan</option>
                            <option value="CN" {{ old('country') === 'CN' ? 'selected' : '' }}>China</option>
                            <option value="IN" {{ old('country') === 'IN' ? 'selected' : '' }}>India</option>
                            <option value="BR" {{ old('country') === 'BR' ? 'selected' : '' }}>Brazil</option>
                        </select>
                    </div>
                    @error('country')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input id="terms" name="terms" type="checkbox" required
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 dark:border-gray-600 rounded">
                    <label for="terms" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                        I agree to the
                        <a href="{{ route('terms') }}" class="text-blue-600 hover:text-blue-500">Terms of Service</a>
                        and
                        <a href="{{ route('privacy') }}" class="text-blue-600 hover:text-blue-500">Privacy Policy</a>
                    </label>
                </div>
                @error('terms')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

