@extends('layouts.app')

@section('title', 'Terms of Service')
@section('description', 'Monero Exchange Terms of Service - understand your rights and responsibilities when trading.')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Terms of Service</h1>
        <p class="mt-4 text-xl text-gray-600 dark:text-gray-400">Last updated: {{ date('F j, Y') }}</p>
    </div>

    <!-- Introduction -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6 mb-12">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-blue-900 dark:text-blue-100 mb-4">Welcome to Monero Exchange</h2>
            <p class="text-blue-800 dark:text-blue-200 max-w-3xl mx-auto">
                These Terms of Service govern your use of Monero Exchange, a peer-to-peer Monero trading platform. 
                By using our service, you agree to these terms.
            </p>
        </div>
    </div>

    <!-- Terms Content -->
    <div class="max-w-4xl mx-auto space-y-8">
        <!-- Section 1 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">1. Acceptance of Terms</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                By accessing or using Monero Exchange ("the Service"), you agree to be bound by these Terms of Service 
                and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited 
                from using or accessing this site.
            </p>
            <p class="text-gray-600 dark:text-gray-400">
                We reserve the right to modify these terms at any time. Your continued use of the Service after any 
                such modifications constitutes your acceptance of the new terms.
            </p>
        </div>

        <!-- Section 2 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">2. Description of Service</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Monero Exchange is a peer-to-peer trading platform that facilitates the exchange of Monero (XMR) 
                between users. Our service includes:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Escrow services to protect both buyers and sellers</li>
                <li>User reputation and feedback systems</li>
                <li>Dispute resolution services</li>
                <li>Communication tools for traders</li>
                <li>Payment method verification</li>
            </ul>
            <p class="text-gray-600 dark:text-gray-400 mt-4">
                We do not hold your funds in our own wallets except for the escrow system during active trades.
            </p>
        </div>

        <!-- Section 3 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">3. User Accounts</h2>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">3.1 Account Creation</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                To use our service, you must create an account with a username, password, and PIN. No email address 
                or personal information is required for basic account creation.
            </p>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">3.2 Account Security</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                You are responsible for maintaining the confidentiality of your account credentials. You agree to:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Keep your password and PIN secure and confidential</li>
                <li>Notify us immediately of any unauthorized use of your account</li>
                <li>Be responsible for all activities that occur under your account</li>
                <li>Use strong, unique passwords and PINs</li>
            </ul>
        </div>

        <!-- Section 4 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">4. Trading Rules and Responsibilities</h2>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">4.1 General Trading Rules</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                When using our platform, you agree to:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Only trade with legitimate, legal funds</li>
                <li>Complete trades in good faith and within agreed timeframes</li>
                <li>Provide accurate information in your offers and communications</li>
                <li>Not engage in fraudulent, deceptive, or illegal activities</li>
                <li>Respect other users and maintain professional communication</li>
            </ul>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">4.2 Escrow System</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Our escrow system is designed to protect both parties in a trade. By using our service, you understand that:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Funds are held in escrow until both parties confirm completion</li>
                <li>Release of funds requires PIN verification</li>
                <li>Disputes may result in funds being held until resolution</li>
                <li>We are not responsible for losses due to user error or negligence</li>
            </ul>
        </div>

        <!-- Section 5 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">5. Prohibited Activities</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                You agree not to use our service for any of the following prohibited activities:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Money laundering or terrorist financing</li>
                <li>Fraud, theft, or other illegal activities</li>
                <li>Market manipulation or price fixing</li>
                <li>Creating multiple accounts to circumvent restrictions</li>
                <li>Attempting to hack or compromise our systems</li>
                <li>Spreading malware or engaging in phishing</li>
                <li>Violating any applicable laws or regulations</li>
                <li>Harassment or abuse of other users</li>
            </ul>
        </div>

        <!-- Section 6 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">6. Fees and Payments</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Our fee structure is transparent and available on our fees page. You agree to:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Pay all applicable fees for completed trades</li>
                <li>Understand that fees are deducted from trade amounts</li>
                <li>Not attempt to circumvent or avoid fees</li>
                <li>Accept that fees may change with notice</li>
            </ul>
        </div>

        <!-- Section 7 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">7. Dispute Resolution</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                If disputes arise between users, we provide a resolution process:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Users can escalate disputes to our moderation team</li>
                <li>Moderators will review evidence and make decisions</li>
                <li>Decisions are final and binding</li>
                <li>We reserve the right to suspend accounts involved in disputes</li>
                <li>Funds may be held during dispute resolution</li>
            </ul>
        </div>

        <!-- Section 8 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">8. Privacy and Data Protection</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We are committed to protecting your privacy. Our Privacy Policy explains how we collect, use, and protect 
                your information. Key points include:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>We collect minimal personal information</li>
                <li>No email address required for basic accounts</li>
                <li>We use strong encryption for sensitive data</li>
                <li>We do not sell or share your personal information</li>
                <li>You can request account deletion at any time</li>
            </ul>
        </div>

        <!-- Section 9 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">9. Limitation of Liability</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                To the maximum extent permitted by law, Monero Exchange shall not be liable for:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Loss of funds due to user error or negligence</li>
                <li>Losses from market volatility or price changes</li>
                <li>Technical issues or system downtime</li>
                <li>Third-party payment method failures</li>
                <li>Regulatory changes or legal restrictions</li>
                <li>Force majeure events beyond our control</li>
            </ul>
        </div>

        <!-- Section 10 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">10. Account Suspension and Termination</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We reserve the right to suspend or terminate accounts that violate these terms:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Immediate suspension for serious violations</li>
                <li>Warning system for minor infractions</li>
                <li>Appeal process for suspended accounts</li>
                <li>Funds will be returned after account closure</li>
                <li>You can close your account at any time</li>
            </ul>
        </div>

        <!-- Section 11 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">11. Compliance and Legal</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                You are responsible for compliance with all applicable laws and regulations in your jurisdiction. 
                This includes but is not limited to:
            </p>
            <ul class="list-disc list-inside text-gray-600 dark:text-gray-400 space-y-2 ml-4">
                <li>Tax obligations on trading activities</li>
                <li>Anti-money laundering (AML) requirements</li>
                <li>Know Your Customer (KYC) regulations</li>
                <li>Cryptocurrency trading restrictions</li>
                <li>International sanctions and embargoes</li>
            </ul>
        </div>

        <!-- Section 12 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">12. Changes to Service</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                We reserve the right to modify, suspend, or discontinue any part of our service at any time. 
                We will provide reasonable notice for significant changes that affect your ability to use the service.
            </p>
        </div>

        <!-- Section 13 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">13. Contact Information</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                If you have questions about these Terms of Service, please contact us through our support system 
                or create a support ticket on the platform.
            </p>
        </div>

        <!-- Section 14 -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">14. Governing Law</h2>
            <p class="text-gray-600 dark:text-gray-400">
                These Terms of Service are governed by and construed in accordance with applicable laws. 
                Any disputes arising from these terms or your use of the service will be resolved through 
                appropriate legal channels.
            </p>
        </div>
    </div>

    <!-- Agreement -->
    <div class="mt-12 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
        <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Agreement</h3>
            <p class="text-gray-600 dark:text-gray-400">
                By using Monero Exchange, you acknowledge that you have read, understood, and agree to be bound by 
                these Terms of Service. If you do not agree to these terms, please do not use our service.
            </p>
        </div>
    </div>
</div>
@endsection

