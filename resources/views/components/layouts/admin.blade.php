@props(['header' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Include Admin Sidebar Component -->
            <x-admin-sidebar />

            <!-- Main Content -->
            <div class="flex-1 lg:ml-64">
                <!-- Top Header -->
                <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
                    <div class="px-4 sm:px-6 py-4">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center space-x-4">
                                <!-- Mobile Menu Button -->
                                <button @click="sidebarOpen = !sidebarOpen" 
                                        class="lg:hidden p-2 rounded-md text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    </svg>
                                </button>
                                
                                <div class="hidden sm:block">
                                    @if($header)
                                        {{ $header }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <a href="/" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                    View Site
                                </a>
                            </div>
                        </div>
                        <!-- Mobile Header Title -->
                        <div class="sm:hidden mt-2">
                            @if($header)
                                {{ $header }}
                            @endif
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4 sm:p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

