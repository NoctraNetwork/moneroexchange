@extends('layouts.app')

@section('title', 'Security')
@section('description', 'Monero Exchange security features and best practices for safe trading.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Security</h1>
        <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">Your security is our top priority</p>
    </div>

    <!-- Security Overview -->
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 mb-12">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-red-900 dark:text-red-100 mb-4">Bank-Grade Security</h2>
            <p class="text-red-800 dark:text-red-200 max-w-3xl mx-auto">
                Monero Exchange implements multiple layers of security to protect your funds and personal information. 
                We use industry-standard encryption, secure protocols, and privacy-first design principles.
            </p>
        </div>
    </div>

    <!-- Security Features -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Security Features</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- No JavaScript -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-red-100 dark:bg-red-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No JavaScript</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Our platform works entirely without JavaScript, eliminating XSS attacks, 
                        clickjacking, and other client-side vulnerabilities.
                    </p>
                </div>
            </div>

            <!-- Escrow Protection -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-green-100 dark:bg-green-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Escrow Protection</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Funds are held in secure escrow using unique Monero subaddresses until both parties confirm completion.
                    </p>
                </div>
            </div>

            <!-- PIN Security -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">PIN Protection</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Sensitive actions require your PIN with exponential backoff and temporary lockouts on failed attempts.
                    </p>
                </div>
            </div>

            <!-- PGP Encryption -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-purple-100 dark:bg-purple-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">PGP Encryption</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Optional PGP keys for encrypted communication and enhanced security verification.
                    </p>
                </div>
            </div>

            <!-- Rate Limiting -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-yellow-100 dark:bg-yellow-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Rate Limiting</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Advanced rate limiting protects against brute force attacks and automated abuse.
                    </p>
                </div>
            </div>

            <!-- Audit Logging -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="text-center">
                    <div class="bg-indigo-100 dark:bg-indigo-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Audit Logging</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Comprehensive logging of all activities for security monitoring and dispute resolution.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Security -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Technical Security</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Encryption -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Encryption Standards</h3>
                <ul class="space-y-3">
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Argon2id password hashing</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">TLS 1.3 for all connections</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">AES-256 database encryption</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Separate PIN encryption with unique salts</span>
                    </li>
                </ul>
            </div>

            <!-- Security Headers -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Security Headers</h3>
                <ul class="space-y-3">
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">Strict Content Security Policy (CSP)</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">HTTP Strict Transport Security (HSTS)</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">X-Frame-Options: DENY</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-gray-600 dark:text-gray-400">X-Content-Type-Options: nosniff</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Security Best Practices -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Security Best Practices</h2>
        
        <div class="space-y-6">
            <!-- For Users -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">For Users</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Account Security</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-2">
                            <li>• Use a strong, unique password</li>
                            <li>• Choose a secure PIN (not common patterns)</li>
                            <li>• Never share your credentials</li>
                            <li>• Log out when finished</li>
                            <li>• Use Tor for enhanced privacy</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Trading Security</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-2">
                            <li>• Verify trader reputation before trading</li>
                            <li>• Keep all communication on the platform</li>
                            <li>• Never send payment outside the platform</li>
                            <li>• Report suspicious behavior immediately</li>
                            <li>• Use PGP for sensitive communications</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- For Traders -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">For Traders</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Monero Security</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-2">
                            <li>• Use a secure Monero wallet</li>
                            <li>• Keep your seed phrase offline</li>
                            <li>• Verify addresses before sending</li>
                            <li>• Use subaddresses for privacy</li>
                            <li>• Keep software updated</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Payment Security</h4>
                        <ul class="text-gray-600 dark:text-gray-400 space-y-2">
                            <li>• Verify payment before releasing XMR</li>
                            <li>• Use secure payment methods</li>
                            <li>• Keep payment records</li>
                            <li>• Be cautious of chargebacks</li>
                            <li>• Report fraud immediately</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Incidents -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Security Incident Response</h2>
        
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">If You Suspect a Security Issue</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Immediate Actions</h4>
                    <ul class="text-gray-600 dark:text-gray-400 space-y-2">
                        <li>1. Change your password immediately</li>
                        <li>2. Change your PIN if compromised</li>
                        <li>3. Check for unauthorized trades</li>
                        <li>4. Contact support immediately</li>
                        <li>5. Document the incident</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Our Response</h4>
                    <ul class="text-gray-600 dark:text-gray-400 space-y-2">
                        <li>• Immediate account suspension if needed</li>
                        <li>• Investigation within 24 hours</li>
                        <li>• User notification of findings</li>
                        <li>• Security improvements if needed</li>
                        <li>• Law enforcement cooperation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Audit -->
    <div class="mb-12">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white text-center mb-8">Security Audits</h2>
        
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="text-center">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Regular Security Assessments</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    We regularly audit our security practices and infrastructure to ensure the highest level of protection.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                            <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Code Audits</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Regular code reviews and security testing</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-green-100 dark:bg-green-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                            <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Penetration Testing</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Third-party security assessments</p>
                    </div>
                    <div class="text-center">
                        <div class="bg-purple-100 dark:bg-purple-900/20 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                            <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-white">Infrastructure</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Server and network security reviews</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Security -->
    <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Report Security Issues</h2>
        <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
            Found a security vulnerability? We want to know about it.
        </p>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6 max-w-2xl mx-auto">
            <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Responsible Disclosure</h3>
            <p class="text-yellow-700 dark:text-yellow-300 mb-4">
                Please report security issues through our support system. We appreciate responsible disclosure 
                and will work with security researchers to fix issues quickly.
            </p>
            <a href="#" class="btn-primary">
                Report Security Issue
            </a>
        </div>
    </div>
</div>
@endsection

