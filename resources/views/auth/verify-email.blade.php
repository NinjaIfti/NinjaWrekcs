<x-guest-layout>
    <div class="w-full">
        <!-- Header with Glitch Effect -->
        <div class="text-center mb-8">
            <div class="mx-auto mb-6 w-20 h-20 bg-gradient-to-br from-violet-600 to-purple-600 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold mb-3">
                <span class="glitch-text-large" data-text="Verify Email">Verify Email</span>
            </h1>
            <p class="text-gray-400 text-lg">Check your inbox to continue</p>
        </div>

        <!-- Information Box -->
        <div class="bg-violet-500/10 border border-violet-500/30 rounded-lg p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-violet-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-white font-semibold mb-2">Almost there!</h3>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Thanks for signing up! Before you can start shopping, please verify your email address by clicking the link we just sent to <span class="text-violet-400 font-semibold">{{ auth()->user()->email }}</span>.
                    </p>
                    <p class="text-gray-400 text-sm mt-3">
                        Didn't receive the email? Check your spam folder or click the button below to resend.
                    </p>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if (session('status') == 'verification-link-sent')
            <div class="mb-6 bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-green-400 text-sm font-medium">
                        A new verification link has been sent to your email address!
                    </p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="space-y-4">
            <!-- Resend Button -->
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-violet-600 to-purple-600 text-white font-bold rounded-lg hover:shadow-lg hover:shadow-violet-500/50 hover:scale-[1.02] transition-all duration-200 relative overflow-hidden group">
                    <span class="relative z-10 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Resend Verification Email
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></span>
                </button>
            </form>

            <!-- Additional Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-violet-500/20">
                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-400 hover:text-white text-sm transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Log Out
                    </button>
                </form>

                <!-- Support -->
                <a href="{{ route('contact') }}" class="text-gray-400 hover:text-white text-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Need Help?
                </a>
            </div>
        </div>

        <!-- Email Tips -->
        <div class="mt-8 bg-black/30 border border-gray-700 rounded-lg p-4">
            <h4 class="text-white font-semibold text-sm mb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                Can't find the email?
            </h4>
            <ul class="space-y-2 text-sm text-gray-400">
                <li class="flex items-start gap-2">
                    <span class="text-violet-400 mt-0.5">•</span>
                    Check your spam or junk folder
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-violet-400 mt-0.5">•</span>
                    Make sure {{ auth()->user()->email }} is correct
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-violet-400 mt-0.5">•</span>
                    Wait a few minutes and check again
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-violet-400 mt-0.5">•</span>
                    Still having trouble? <a href="{{ route('contact') }}" class="text-violet-400 hover:text-violet-300 underline">Contact us</a>
                </li>
            </ul>
        </div>
    </div>
</x-guest-layout>
