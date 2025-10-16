@extends('layouts.app')

@section('title', 'PIN Locked')
@section('description', 'Your PIN has been temporarily locked due to too many failed attempts.')

@section('content')
<div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                PIN Temporarily Locked
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Your PIN has been temporarily locked due to too many failed attempts.
            </p>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Please wait before trying again. The lockout will expire in:
                </p>
                <p class="mt-2 text-lg font-medium text-gray-900 dark:text-white">
                    @if(session('lockout_time'))
                        {{ gmdate('H:i:s', session('lockout_time')) }}
                    @else
                        Unknown
                    @endif
                </p>
                
                <div class="mt-6">
                    <a href="{{ route('logout') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sign out
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

