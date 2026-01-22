<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agent Code - NinjaWrecks</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <!-- Minimal Navbar with Logo Only -->
    <nav class="fixed w-full bg-black/95 backdrop-blur-xl shadow-lg z-50 border-b border-violet-500/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center items-center h-20">
                <a href="/" class="flex items-center space-x-3">
                    <img src="{{ asset('img/fav.png') }}" alt="NinjaWrecks" class="h-12 w-auto">
                    <span class="text-2xl font-bold glitch-text" data-text="NinjaWrecks">NinjaWrecks</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black flex items-center justify-center">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-12">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">
                    <span class="glitch-text" data-text="Welcome back, Agent.">Welcome back, Agent.</span>
                </h1>
                <div class="mt-8 p-6 bg-violet-500/10 border border-violet-500/30 rounded-lg">
                    <p class="text-xl md:text-2xl text-gray-300 mb-4">Your 10% code is:</p>
                    <p class="text-4xl md:text-5xl font-bold text-violet-400 glitch-text" data-text="AGENT10">AGENT10</p>
                </div>
            </div>
        </div>
    </section>

    @include('home.styles')
</body>
</html>
