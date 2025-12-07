<x-guest-layout>
    <div class="w-full">
        <!-- Header with Glitch Effect -->
        <div class="text-center mb-8">
            <h1 class="text-4xl md:text-5xl font-bold mb-3">
                <span class="glitch-text-large" data-text="Welcome Back">Welcome Back</span>
            </h1>
            <p class="text-gray-400 text-lg">Sign in to your Valorant collectibles account</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-violet-400" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" class="mb-2 text-gray-300" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                        </svg>
                    </div>
                    <x-text-input 
                        id="email" 
                        class="block mt-1 w-full pl-12 py-3 bg-black/50 border-2 border-violet-500/30 text-white placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500/50 rounded-lg transition duration-150" 
                        type="email" 
                        name="email" 
                        :value="old('email')" 
                        required 
                        autofocus 
                        autocomplete="username"
                        placeholder="Enter your email" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" class="mb-2 text-gray-300" />
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <x-text-input 
                        id="password" 
                        class="block mt-1 w-full pl-12 py-3 bg-black/50 border-2 border-violet-500/30 text-white placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500/50 rounded-lg transition duration-150"
                        type="password"
                        name="password"
                        required 
                        autocomplete="current-password"
                        placeholder="Enter your password" />
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input 
                        id="remember_me" 
                        type="checkbox" 
                        class="rounded border-violet-500/50 bg-black/50 text-violet-600 shadow-sm focus:ring-violet-500 focus:ring-offset-0 w-4 h-4" 
                        name="remember">
                    <span class="ms-2 text-sm text-gray-300 group-hover:text-violet-400 transition-colors">{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-violet-400 hover:text-violet-300 transition duration-150 relative group" href="{{ route('password.request') }}">
                        {{ __('Forgot password?') }}
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" class="w-full justify-center py-3 text-base font-semibold bg-gradient-to-r from-violet-600 to-purple-600 text-white rounded-lg hover:shadow-lg hover:shadow-violet-500/50 hover:scale-105 transition-all relative overflow-hidden group">
                    <span class="relative z-10 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        {{ __('Log in') }}
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-purple-600 to-violet-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </button>
            </div>

            <!-- Register Link -->
            <div class="text-center pt-6 border-t border-violet-500/20">
                <p class="text-sm text-gray-400">
                    Don't have an account?
                    <a href="{{ route('register') }}" class="font-medium text-violet-400 hover:text-violet-300 transition duration-150 relative group">
                        {{ __('Sign up') }}
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-violet-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                </p>
            </div>
        </form>
    </div>
</x-guest-layout>
