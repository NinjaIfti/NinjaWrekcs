<x-admin-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                {{ __('Incomplete Orders') }}
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Abandoned Checkouts: <span class="font-bold text-orange-600 dark:text-orange-400">{{ $incompleteOrders->count() }}</span>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <!-- View Toggle (matches the tabs on the main Orders page) -->
            <div class="mb-6 flex flex-wrap gap-3 items-center">
                <div class="inline-flex rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-1">
                    <a href="{{ route('admin.orders', ['view' => 'active']) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Active Orders
                        </span>
                    </a>
                    <a href="{{ route('admin.orders', ['view' => 'preorder']) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            Pre-Order
                        </span>
                    </a>
                    <a href="{{ route('admin.orders', ['view' => 'hidden']) }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                            Hidden Orders
                        </span>
                    </a>
                    <a href="{{ route('admin.orders.incomplete') }}"
                       class="px-4 py-2 rounded-md text-sm font-medium transition bg-white dark:bg-gray-800 text-orange-600 dark:text-orange-400 shadow-sm">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            Incomplete
                        </span>
                    </a>
                </div>
            </div>

            <div class="mb-6 p-4 bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-orange-600 dark:text-orange-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-orange-800 dark:text-orange-200 font-medium">Customers who started checkout but didn't place an order</p>
                    <p class="text-orange-700 dark:text-orange-300 text-sm mt-1">Captured automatically as customers type their name, phone, or address on the checkout page. Useful for following up on abandoned checkouts. An entry disappears from here once that customer completes their order.</p>
                </div>
            </div>

            @if($incompleteOrders->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 text-lg">No incomplete checkouts found.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Customer</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Address</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cart</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Last Activity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($incompleteOrders as $entry)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white align-top">
                                        {{ $entry->name ?: '—' }}
                                        @if($entry->user)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Logged in: {{ $entry->user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        @if($entry->phone)
                                            <div class="flex items-center gap-2">
                                                <a href="tel:{{ $entry->phone }}" class="text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400">{{ $entry->phone }}</a>
                                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $entry->phone) }}" target="_blank" rel="noopener" class="text-green-600 hover:text-green-700 dark:text-green-400" title="WhatsApp">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.116.552 4.104 1.516 5.828L0 24l6.35-1.492A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.938a9.9 9.9 0 01-5.05-1.386l-.362-.215-3.74.879.89-3.65-.236-.375A9.907 9.907 0 012.062 12C2.062 6.51 6.51 2.062 12 2.062S21.938 6.51 21.938 12 17.49 21.938 12 21.938z"/></svg>
                                                </a>
                                            </div>
                                        @else
                                            —
                                        @endif
                                        @if($entry->email)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $entry->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white align-top max-w-xs">
                                        {{ $entry->address ?: '—' }}
                                        @if($entry->delivery_location)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $entry->delivery_location)) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white align-top">
                                        @if(!empty($entry->cart_snapshot))
                                            <ul class="space-y-1">
                                                @foreach($entry->cart_snapshot as $cartItem)
                                                    <li class="text-xs">{{ $cartItem['name'] ?? 'Item' }} × {{ $cartItem['quantity'] ?? 1 }}</li>
                                                @endforeach
                                            </ul>
                                            @if($entry->subtotal)
                                                <div class="text-xs font-semibold mt-1">৳{{ number_format($entry->subtotal, 2) }}</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Empty</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 align-top whitespace-nowrap">
                                        {{ $entry->last_activity_at?->diffForHumans() ?? $entry->updated_at->diffForHumans() }}
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <form action="{{ route('admin.orders.incomplete.delete', $entry) }}" method="POST" onsubmit="return confirm('Remove this incomplete checkout entry?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-600 dark:text-red-400 rounded-lg transition text-sm font-medium">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
