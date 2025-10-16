@extends('layouts.app')

@section('title', 'Privacy Policy')
@section('description', 'Monero Exchange Privacy Policy - how we protect your privacy and handle your data.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Privacy Policy</h1>
        <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">Last updated: {{ date('F j, Y') }}</p>
    </div>

    <!-- Introduction -->
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 mb-12">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-green-900 dark:text-green-100 mb-4">Your Privacy Matters</h2>
            <p class="text-green-800 dark:text-green-200 max-w-3xl mx-auto">
                At Monero Exchange, we are committed to protecting your privacy and personal information. 
                This policy explains how we collect, use, and safeguard your data.
            </p>
        </div>
    </div>

    <!-- Privacy Content -->
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Section 1 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">1. Information We Collect</h2>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">1.1 Account Information</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                To create an account, we collect only the minimum information necessary:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li><strong>Username:</strong> Your chosen display name (no personal information required)</li>
                <li><strong>Password:</strong> Encrypted and stored securely using Argon2id hashing</li>
                <li><strong>PIN:</strong> Encrypted separately with different salt for additional security</li>
                <li><strong>Country:</strong> Optional, used for compliance and statistics only</li>
            </ul>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">1.2 Trading Information</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                When you trade, we collect:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Trade amounts and prices (for escrow and fee calculation)</li>
                <li>Payment method preferences</li>
                <li>Trade communications (encrypted if PGP is used)</li>
                <li>Dispute information and resolution details</li>
            </ul>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">1.3 Technical Information</h3>
            <p class="text-gray-600 dark:text-gray-400">
                We automatically collect:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>IP address (hashed for security logs)</li>
                <li>Browser type and version</li>
                <li>Access times and dates</li>
                <li>Tor usage indicators (for statistics)</li>
            </ul>
        </div>

        <!-- Section 2 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">2. What We DON'T Collect</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We deliberately avoid collecting sensitive personal information:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li><strong>No email addresses</strong> - Not required for basic accounts</li>
                <li><strong>No real names</strong> - Usernames only</li>
                <li><strong>No phone numbers</strong> - Not collected</li>
                <li><strong>No government IDs</strong> - Not required for basic trading</li>
                <li><strong>No bank account details</strong> - Handled between users</li>
                <li><strong>No Monero wallet seeds</strong> - We never see your private keys</li>
            </ul>
        </div>

        <!-- Section 3 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">3. How We Use Your Information</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We use your information only for legitimate business purposes:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li><strong>Account Management:</strong> To maintain your account and provide services</li>
                <li><strong>Escrow Services:</strong> To facilitate secure trades between users</li>
                <li><strong>Security:</strong> To detect and prevent fraud and abuse</li>
                <li><strong>Dispute Resolution:</strong> To resolve conflicts between users</li>
                <li><strong>Platform Improvement:</strong> To enhance our service (anonymized data only)</li>
                <li><strong>Legal Compliance:</strong> To meet regulatory requirements when necessary</li>
            </ul>
        </div>

        <!-- Section 4 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">4. Data Security</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We implement multiple layers of security to protect your data:
            </p>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">4.1 Encryption</h3>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Passwords hashed with Argon2id (industry standard)</li>
                <li>PINs encrypted separately with different salts</li>
                <li>All data encrypted in transit (HTTPS/TLS)</li>
                <li>Database encryption at rest</li>
            </ul>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">4.2 Access Controls</h3>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Strict access controls and authentication</li>
                <li>Regular security audits and updates</li>
                <li>Minimal data retention policies</li>
                <li>Secure data disposal procedures</li>
            </ul>
        </div>

        <!-- Section 5 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">5. Information Sharing</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We do not sell, trade, or rent your personal information to third parties. We may share information only in these limited circumstances:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li><strong>Legal Requirements:</strong> When required by law or court order</li>
                <li><strong>Safety and Security:</strong> To protect users from fraud or abuse</li>
                <li><strong>Service Providers:</strong> With trusted partners who help operate our platform (under strict confidentiality)</li>
                <li><strong>Business Transfers:</strong> In case of merger or acquisition (with user notification)</li>
            </ul>
        </div>

        <!-- Section 6 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">6. Data Retention</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We retain your information only as long as necessary:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li><strong>Account Data:</strong> Until account deletion or 3 years of inactivity</li>
                <li><strong>Trade Records:</strong> 7 years for legal and tax compliance</li>
                <li><strong>Security Logs:</strong> 1 year (IP addresses hashed)</li>
                <li><strong>Communication:</strong> Until trade completion or 1 year</li>
            </ul>
        </div>

        <!-- Section 7 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">7. Your Rights</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                You have the following rights regarding your personal information:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li><strong>Access:</strong> Request a copy of your personal data</li>
                <li><strong>Correction:</strong> Update or correct inaccurate information</li>
                <li><strong>Deletion:</strong> Request deletion of your account and data</li>
                <li><strong>Portability:</strong> Export your data in a standard format</li>
                <li><strong>Objection:</strong> Object to certain processing activities</li>
                <li><strong>Restriction:</strong> Request limitation of data processing</li>
            </ul>
        </div>

        <!-- Section 8 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">8. Cookies and Tracking</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We use minimal cookies and tracking:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li><strong>Essential Cookies:</strong> For login sessions and security</li>
                <li><strong>No Analytics:</strong> We don't use Google Analytics or similar tracking</li>
                <li><strong>No Advertising:</strong> No third-party advertising or tracking</li>
                <li><strong>No Social Media:</strong> No social media integration or tracking</li>
            </ul>
        </div>

        <!-- Section 9 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">9. International Transfers</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Your data may be processed in different countries. We ensure adequate protection through:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Standard contractual clauses</li>
                <li>Adequacy decisions by relevant authorities</li>
                <li>Appropriate safeguards and security measures</li>
                <li>Regular compliance reviews</li>
            </ul>
        </div>

        <!-- Section 10 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">10. Children's Privacy</h2>
            <p class="text-gray-600 dark:text-gray-400">
                Our service is not intended for children under 18. We do not knowingly collect personal information 
                from children. If we become aware that we have collected such information, we will delete it immediately.
            </p>
        </div>

        <!-- Section 11 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">11. Changes to This Policy</h2>
            <p class="text-gray-600 dark:text-gray-400">
                We may update this Privacy Policy from time to time. We will notify you of any material changes 
                by posting the new policy on our website and updating the "Last updated" date. Your continued use 
                of our service after such changes constitutes acceptance of the updated policy.
            </p>
        </div>

        <!-- Section 12 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">12. Contact Us</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                If you have questions about this Privacy Policy or our data practices, please contact us:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Through our support system on the platform</li>
                <li>Create a support ticket for privacy-related inquiries</li>
                <li>We will respond within 30 days of receiving your request</li>
            </ul>
        </div>
    </div>

    <!-- Privacy Principles -->
    <div class="mt-12 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
        <div class="text-center">
            <h3 class="text-xl font-bold text-blue-900 dark:text-blue-100 mb-4">Our Privacy Principles</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100">Minimal Data</h4>
                    <p class="text-sm text-blue-800 dark:text-blue-200">Collect only what's necessary</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100">Strong Security</h4>
                    <p class="text-sm text-blue-800 dark:text-blue-200">Protect with encryption</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100">Transparency</h4>
                    <p class="text-sm text-blue-800 dark:text-blue-200">Clear about our practices</p>
                </div>
                <div class="text-center">
                    <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full w-12 h-12 flex items-center justify-center mx-auto mb-2">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100">Your Control</h4>
                    <p class="text-sm text-blue-800 dark:text-blue-200">You control your data</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

