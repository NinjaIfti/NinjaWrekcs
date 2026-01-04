<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('User Orders') }}
                </h2>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Total Orders: <span class="font-bold text-gray-900 dark:text-white">{{ $orders->count() }}</span>
                </div>
            </div>
            <a href="{{ route('admin.orders.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Manual Order
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                {{ session('warning') }}
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- Filters and Export -->
            <div class="mb-6 flex flex-wrap gap-4 justify-between items-center">
                <form method="GET" action="{{ route('admin.orders') }}" class="flex flex-wrap gap-4">
                    <select name="status" class="border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                        <option value="">All Status</option>
                        <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $selectedStatus === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ $selectedStatus === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $selectedStatus === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $selectedStatus === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $selectedStatus === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Filter
                    </button>
                    @if($selectedStatus)
                        <a href="{{ route('admin.orders') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                            Clear Filter
                        </a>
                    @endif
                </form>
                
                <a href="{{ route('admin.orders.export', ['status' => $selectedStatus]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download Excel
                </a>
            </div>

            <!-- Orders List -->
            @if($orders->isEmpty())
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No orders found.</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($orders as $order)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <!-- Order Header -->
                            <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                            Order #{{ $order->id }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Placed on {{ $order->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <select name="status" onchange="this.form.submit()" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm font-semibold
                                                {{ $order->status === 'pending' ? 'text-yellow-600' : '' }}
                                                {{ $order->status === 'confirmed' ? 'text-blue-600' : '' }}
                                                {{ $order->status === 'processing' ? 'text-purple-600' : '' }}
                                                {{ $order->status === 'shipped' ? 'text-indigo-600' : '' }}
                                                {{ $order->status === 'delivered' ? 'text-green-600' : '' }}
                                                {{ $order->status === 'cancelled' ? 'text-red-600' : '' }}">
                                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Content -->
                            <div class="p-6">
                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Customer Information -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Customer Information</h4>
                                        <div class="space-y-2 text-sm">
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Name:</span> <span class="text-gray-900 dark:text-white">{{ $order->name }}</span></p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Email:</span> <span class="text-gray-900 dark:text-white">{{ $order->email }}</span></p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Phone:</span> <span class="text-gray-900 dark:text-white">{{ $order->phone }}</span></p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Address:</span> <span class="text-gray-900 dark:text-white">{{ $order->address }}</span></p>
                                            @if($order->user)
                                                <p><span class="font-medium text-gray-700 dark:text-gray-300">User ID:</span> <span class="text-gray-900 dark:text-white">{{ $order->user->id }} ({{ $order->user->email }})</span></p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Payment Information -->
                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Payment Information</h4>
                                        <div class="space-y-2 text-sm">
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Payment Method:</span> <span class="text-gray-900 dark:text-white uppercase">{{ $order->payment_method }}</span></p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Transaction Number:</span> <span class="text-gray-900 dark:text-white">{{ $order->transaction_number }}</span></p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Sending Number:</span> <span class="text-gray-900 dark:text-white">{{ $order->sending_number }}</span></p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Subtotal:</span> <span class="text-gray-900 dark:text-white">৳{{ number_format($order->subtotal, 2) }}</span></p>
                                            @if($order->delivery_charge > 0)
                                                <p><span class="font-medium text-gray-700 dark:text-gray-300">Delivery Charge:</span> <span class="text-blue-600 dark:text-blue-400">+৳{{ number_format($order->delivery_charge, 2) }}</span> <span class="text-xs text-gray-500">({{ ucfirst(str_replace('_', ' ', $order->delivery_location ?? 'N/A')) }})</span></p>
                                            @endif
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Discount:</span> <span class="text-green-600 dark:text-green-400">-৳{{ number_format($order->discount, 2) }}</span></p>
                                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Total:</span> <span class="text-lg font-bold text-gray-900 dark:text-white">৳{{ number_format($order->total, 2) }}</span></p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="mt-6">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Order Items</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Price</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($order->items as $item)
                                                    <tr>
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                            {{ $item->product_name }}
                                                            @if($item->product)
                                                                <span class="text-gray-500 dark:text-gray-400">(ID: {{ $item->product_id }})</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">৳{{ number_format($item->price, 2) }}</td>
                                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $item->quantity }}</td>
                                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">৳{{ number_format($item->subtotal, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Additional Notes -->
                                @if($order->notes)
                                    <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                        <p class="text-sm"><span class="font-medium text-gray-700 dark:text-gray-300">Notes:</span> <span class="text-gray-900 dark:text-white">{{ $order->notes }}</span></p>
                                    </div>
                                @endif

                                <!-- Order Metadata -->
                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                                    <div>
                                        <p>Order ID: {{ $order->id }}</p>
                                        <p>Created: {{ $order->created_at->format('M d, Y h:i A') }}</p>
                                        <p>Updated: {{ $order->updated_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                    <div class="text-right flex items-center gap-4">
                                        <div>
                                            <p>Save Info: {{ $order->save_info ? 'Yes' : 'No' }}</p>
                                            <p>Terms Accepted: {{ $order->terms_accepted ? 'Yes' : 'No' }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('admin.orders.edit', $order) }}" class="px-3 py-2 bg-blue-500/10 hover:bg-blue-500/20 border border-blue-500/30 text-blue-600 dark:text-blue-400 rounded-lg transition text-sm font-medium flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.orders.delete', $order) }}" method="POST" onsubmit="return confirm('Hide this order from admin panel? The customer can still see it in their order history.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-600 dark:text-red-400 rounded-lg transition text-sm font-medium flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Hide
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
