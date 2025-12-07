<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'NinjaWrekcs') }} - Login</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-black text-white">
        <!-- Violet Glitch Background -->
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden bg-gradient-to-br from-black via-violet-950 to-purple-950">
            <!-- Animated Background Effects -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-violet-500/20 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute top-1/2 left-1/2 w-96 h-96 bg-fuchsia-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
            </div>

            <!-- Glitch Grid Overlay -->
            <div class="absolute inset-0 opacity-10">
                <div class="grid-pattern"></div>
            </div>

            <div class="relative w-full z-10">
                <!-- Logo with Glitch Effect -->
                <div class="flex justify-center mb-8">
                    <a href="/" class="flex items-center space-x-3 group">
                        <span class="text-3xl font-bold glitch-text" data-text="NinjaWrekcs">NinjaWrekcs</span>
                    </a>
                </div>

                <!-- Card -->
                <div class="w-full sm:max-w-md mx-auto px-6 sm:px-0">
                    <div class="bg-black/50 backdrop-blur-xl shadow-2xl rounded-2xl border border-violet-500/30 overflow-hidden relative">
                        <!-- Glitch overlay on card -->
                        <div class="absolute inset-0 glitch-bg opacity-30"></div>
                        
                        <div class="px-8 py-10 relative z-10">
                            {{ $slot }}
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-8 text-sm text-gray-400">
                    <p>&copy; {{ date('Y') }} {{ config('app.name', 'NinjaWrekcs') }}. All rights reserved.</p>
                </div>
            </div>
        </div>

        <!-- Include Glitch Styles -->
        @include('home.styles')
        
        <!-- Password Input Styles -->
        <style>
            input[type="password"] {
                -webkit-text-security: disc !important;
                text-security: disc !important;
                font-family: 'text-security-disc', monospace;
            }
            
            input[type="password"]::placeholder {
                -webkit-text-security: none !important;
                text-security: none !important;
            }
        </style>
    </body>
</html>
