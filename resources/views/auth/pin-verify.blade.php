@extends('layouts.app')

@section('title', 'Verify PIN')
@section('description', 'Enter your PIN to continue with sensitive operations.')

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
            Verify Your PIN
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
            Enter your PIN to continue with this action
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <form class="space-y-6" method="POST" action="{{ route('pin.verify') }}">
                @csrf

                <div>
                    <label for="pin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        PIN
                    </label>
                    <div class="mt-1">
                        <input id="pin" name="pin" type="password" autocomplete="off" required
                               class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('pin') border-red-300 @enderror">
                    </div>
                    @error('pin')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Verify PIN
                    </button>
                </div>

                <div class="text-center">
                    <a href="{{ route('logout') }}" class="text-sm text-blue-600 hover:text-blue-500">
                        Sign out
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

