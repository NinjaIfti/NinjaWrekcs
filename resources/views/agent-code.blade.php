<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Giveaway Entry - NinjaWrekcs</title>
    <link rel="icon" type="image/png" href="{{ asset('img/fav.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-black text-white">
    <nav class="fixed w-full bg-black/95 backdrop-blur-xl shadow-lg z-50 border-b border-violet-500/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center items-center h-20">
                <a href="/" class="flex items-center space-x-3">
                    <img src="{{ asset('img/fav.png') }}" alt="NinjaWrekcs" class="h-12 w-auto">
                    <span class="text-2xl font-bold glitch-text" data-text="NinjaWrekcs">NinjaWrekcs</span>
                </a>
            </div>
        </div>
    </nav>

    <section class="pt-32 pb-20 min-h-screen bg-gradient-to-b from-black via-violet-950/50 to-black">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-black/50 backdrop-blur-xl rounded-2xl border border-violet-500/30 p-6 md:p-8">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="glitch-text" data-text="Giveaway Entry">Giveaway Entry</span>
                </h1>
                <p class="text-gray-300 mb-6">
                    Search by phone number to find delivered orders and enter each order separately.
                </p>

                @if(session('success'))
                    <div class="mb-4 rounded-lg border border-green-500/30 bg-green-500/10 px-4 py-3 text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-4 rounded-lg border border-yellow-500/30 bg-yellow-500/10 px-4 py-3 text-yellow-300">
                        {{ session('warning') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('agent-code.search') }}" method="GET" class="mb-6">
                    <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">Phone number</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            id="phone"
                            name="phone"
                            type="text"
                            value="{{ old('phone', $searchPhone ?? '') }}"
                            placeholder="01XXXXXXXXX"
                            required
                            class="w-full rounded-lg border border-violet-500/30 bg-black/50 px-4 py-3 text-white focus:border-violet-400 focus:outline-none"
                        >
                        <button type="submit" class="rounded-lg bg-violet-600 hover:bg-violet-700 px-6 py-3 font-semibold transition">
                            Search Order
                        </button>
                    </div>
                </form>

                @php
                    $orders = $orders ?? collect();
                    $enteredOrderIds = $enteredOrderIds ?? [];
                @endphp

                @if(isset($searchPhone))
                    <div class="mb-4 text-sm text-gray-300">
                        Found <span class="font-semibold text-white">{{ $orders->count() }}</span> delivered order(s) for
                        <span class="font-semibold text-violet-300">{{ $searchPhone }}</span>.
                    </div>
                @endif

                @if($orders->isNotEmpty())
                    <div class="overflow-x-auto rounded-xl border border-violet-500/20">
                        <table class="min-w-full divide-y divide-violet-500/20">
                            <thead class="bg-violet-500/10">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wide text-gray-300">Invoice</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wide text-gray-300">Date</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wide text-gray-300">Phone</th>
                                    <th class="px-4 py-3 text-left text-xs uppercase tracking-wide text-gray-300">Status</th>
                                    <th class="px-4 py-3 text-right text-xs uppercase tracking-wide text-gray-300">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-violet-500/10">
                                @foreach($orders as $order)
                                    @php
                                        $isEntered = in_array($order->id, $enteredOrderIds, true);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-semibold text-violet-300">INV-{{ $order->id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-200">{{ $order->created_at->format('d M Y h:i A') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-200">{{ $order->phone }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="rounded-full bg-green-500/20 text-green-300 px-2 py-1 text-xs font-semibold uppercase">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if($isEntered)
                                                <button type="button" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold cursor-not-allowed" disabled>
                                                    Already Entered
                                                </button>
                                            @else
                                                <form action="{{ route('agent-code.enter') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                                                    <button type="submit" class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-sm font-semibold transition">
                                                        Click to Enter
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif(isset($searchPhone))
                    <div class="rounded-lg border border-violet-500/20 bg-violet-500/5 px-4 py-4 text-sm text-gray-300">
                        No delivered orders found for this number.
                    </div>
                @endif
            </div>
        </div>
    </section>

    @include('home.styles')
</body>
</html>
