<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Notifications - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    @include('home.components.navigation')
    
    <section class="pt-24 md:pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="glitch-text" data-text="Notifications">Notifications</span>
                </h1>
                <p class="text-xl text-gray-400">Stay updated with your orders and special offers</p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Filters & Actions -->
            <div class="mb-6 bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-4">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('notifications.index', ['filter' => 'all']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'all' ? 'bg-violet-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                            All ({{ $notifications->total() }})
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'unread' ? 'bg-violet-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                            Unread ({{ $unreadCount }})
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'order_updates']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'order_updates' ? 'bg-violet-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                            📦 Orders
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'offers']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'offers' ? 'bg-violet-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                            🎁 Offers
                        </a>
                        <a href="{{ route('notifications.index', ['filter' => 'products']) }}" 
                           class="px-4 py-2 rounded-lg text-sm font-semibold transition {{ $filter === 'products' ? 'bg-violet-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                            ✨ Products
                        </a>
                    </div>

                    <!-- Mark All Read -->
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg text-sm font-semibold transition">
                                Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Notifications List -->
            <div class="space-y-4">
                @forelse($notifications as $notification)
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 overflow-hidden {{ !$notification->is_read ? 'ring-2 ring-violet-500/50' : '' }}">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl bg-{{ $notification->color }}-500/20 border border-{{ $notification->color }}-500/30">
                                        {{ $notification->icon }}
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-semibold text-white mb-1">
                                                {{ $notification->title }}
                                                @if(!$notification->is_read)
                                                    <span class="ml-2 inline-block w-2 h-2 bg-violet-500 rounded-full animate-pulse"></span>
                                                @endif
                                            </h3>
                                            <p class="text-gray-400 mb-2">{{ $notification->message }}</p>
                                            <p class="text-sm text-gray-500">{{ $notification->time_ago }}</p>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-2">
                                            @if(!$notification->is_read)
                                                <button onclick="markAsRead({{ $notification->id }})" 
                                                        class="p-2 text-gray-400 hover:text-white transition" 
                                                        title="Mark as read">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            @endif
                                            
                                            <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-2 text-gray-400 hover:text-red-400 transition" 
                                                        title="Delete"
                                                        onclick="return confirm('Delete this notification?')">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    @if($notification->action_url)
                                        <a href="{{ $notification->action_url }}" 
                                           class="mt-4 inline-block px-4 py-2 bg-violet-600 hover:bg-violet-700 rounded-lg text-sm font-semibold transition"
                                           onclick="markAsRead({{ $notification->id }})">
                                            View Details →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-12 text-center">
                        <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">No Notifications</h3>
                        <p class="text-gray-400">You're all caught up! Check back later for updates.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </section>

    <script>
        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>










