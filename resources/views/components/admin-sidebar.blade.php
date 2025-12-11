@props(['sidebarOpen' => false])

<!-- Mobile Overlay -->
<div x-show="sidebarOpen" 
     @click="sidebarOpen = false"
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-600 bg-opacity-75 z-20 lg:hidden"
     style="display: none;">
</div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 fixed h-screen overflow-y-auto z-30 transition-transform duration-300 ease-in-out lg:translate-x-0">
    <div class="p-6">
        <!-- Logo and Close Button -->
        <div class="mb-8 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                <span class="text-xl font-bold text-gray-900 dark:text-white">Admin Panel</span>
            </a>
            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" 
                    class="lg:hidden p-2 rounded-md text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               @click="sidebarOpen = false"
               class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- User Orders -->
            <a href="{{ route('admin.orders') }}" 
               @click="sidebarOpen = false"
               class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('admin.orders') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>User Orders</span>
            </a>

            <!-- Users -->
            <a href="{{ route('admin.users') }}" 
               @click="sidebarOpen = false"
               class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('admin.users') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4a2 2 0 00-2-2H4a2 2 0 00-2 2v16h5m3-9l2 2 4-4m-9 11h4"/>
                </svg>
                <span>Users</span>
            </a>

            <!-- Products -->
            <a href="{{ route('admin.products') }}" 
               @click="sidebarOpen = false"
               class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('admin.products') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Products</span>
            </a>

            <!-- Featured Products -->
            <a href="{{ route('admin.featured-products') }}" 
               @click="sidebarOpen = false"
               class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('admin.featured-products') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <span>Featured Products</span>
            </a>

            <!-- Visitors -->
            <a href="{{ route('admin.visitors') }}" 
               @click="sidebarOpen = false"
               class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('admin.visitors') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <span>Visitors</span>
            </a>

            <!-- Financial Overview -->
            <a href="{{ route('admin.financial') }}" 
               @click="sidebarOpen = false"
               class="flex items-center px-4 py-3 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition {{ request()->routeIs('admin.financial') ? 'bg-gray-100 dark:bg-gray-700 font-semibold' : '' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Financial Overview</span>
            </a>
        </nav>
    </div>

    <!-- User Section -->
    <div class="absolute bottom-0 w-64 p-6 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                    <span class="text-gray-600 dark:text-gray-300 font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
            <div class="ml-3 flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
            </div>
        </div>
        <div class="space-y-2">
            <a href="{{ route('profile.edit') }}" 
               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                Profile Settings
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                    Logout
                </button>
            </form>
        </div>
    </div>
</aside>

