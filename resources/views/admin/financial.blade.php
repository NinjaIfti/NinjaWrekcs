<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Financial Overview') }}
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Last Updated: {{ now()->format('M d, Y h:i A') }}
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Key Performance Indicators -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-xl p-6 border border-blue-100 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Key Performance Indicators
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Revenue Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Total Revenue</p>
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-1">৳{{ number_format($totalRevenue, 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This Month: ৳{{ number_format($thisMonthRevenue, 0) }}</p>
                </div>

                <!-- Expenses Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Total Expenses</p>
                        <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-1">৳{{ number_format($totalExpenses, 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This Month: ৳{{ number_format($thisMonthExpenses, 0) }}</p>
                </div>

                <!-- Pure Profit Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border-2 {{ $pureProfit >= 0 ? 'border-blue-300 dark:border-blue-700' : 'border-orange-300 dark:border-orange-700' }}">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Pure Profit</p>
                        <div class="p-2 {{ $pureProfit >= 0 ? 'bg-blue-100 dark:bg-blue-900/30' : 'bg-orange-100 dark:bg-orange-900/30' }} rounded-lg">
                            <svg class="w-5 h-5 {{ $pureProfit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold {{ $pureProfit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-orange-600 dark:text-orange-400' }} mb-1">৳{{ number_format($pureProfit, 0) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This Month: ৳{{ number_format($thisMonthProfit, 0) }}</p>
                </div>

                <!-- Profit Margin Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 uppercase tracking-wide">Profit Margin</p>
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ $totalRevenue > 0 ? number_format(($pureProfit / $totalRevenue) * 100, 1) : 0 }}%</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalOrders }} Total Orders</p>
                </div>
            </div>
        </div>

        <!-- Financial Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Cost Structure -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Cost Structure Analysis
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Product Costs -->
                        <div class="flex items-center justify-between p-4 bg-orange-50 dark:bg-orange-900/10 rounded-lg border border-orange-200 dark:border-orange-800">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Product Costs (COGS)</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cost of goods sold</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-gray-900 dark:text-white">৳{{ number_format($totalProductCosts, 0) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalExpenses > 0 ? number_format(($totalProductCosts / $totalExpenses) * 100, 0) : 0 }}% of total</p>
                            </div>
                        </div>

                        <!-- Operational Expenses -->
                        <div class="flex items-center justify-between p-4 bg-blue-50 dark:bg-blue-900/10 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Operational Expenses</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ads, shipping, packaging, etc.</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-gray-900 dark:text-white">৳{{ number_format($totalOperationalExpenses, 0) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalExpenses > 0 ? number_format(($totalOperationalExpenses / $totalExpenses) * 100, 0) : 0 }}% of total</p>
                            </div>
                        </div>

                        <!-- View Details Link -->
                        <div class="pt-2">
                            <a href="{{ route('admin.costing') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                View Detailed Costing & Expenses
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Metrics -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Business Metrics
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Average Order Value -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Average Order Value</span>
                                <span class="text-lg font-bold text-gray-900 dark:text-white">৳{{ number_format($averageOrder, 0) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                @php
                                    $maxAvgOrder = $maxOrderValue > 0 ? $maxOrderValue : 1;
                                    $avgOrderPercent = min(100, ($averageOrder / $maxAvgOrder) * 100);
                                @endphp
                                <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $avgOrderPercent }}%"></div>
                            </div>
                        </div>

                        <!-- Total Orders -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Orders Completed</span>
                                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($totalOrders) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                @php
                                    $orderGoal = $monthlyOrderGoal > 0 ? $monthlyOrderGoal : $totalOrders;
                                    $ordersPercent = $orderGoal > 0 ? min(100, ($totalOrders / $orderGoal) * 100) : 0;
                                @endphp
                                <div class="bg-green-600 h-2 rounded-full" style="width: {{ $ordersPercent }}%"></div>
                            </div>
                        </div>

                        <!-- Revenue per Order -->
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue per Order</span>
                                <span class="text-lg font-bold text-blue-600 dark:text-blue-400">৳{{ $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 0) : 0 }}</span>
                            </div>
                        </div>

                        <!-- Profit per Order -->
                        <div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Profit per Order</span>
                                <span class="text-lg font-bold {{ $pureProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">৳{{ $totalOrders > 0 ? number_format($pureProfit / $totalOrders, 0) : 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Alert -->
        @if($totalRevenue > 0)
            @php
                $profitMarginPercent = ($pureProfit / $totalRevenue) * 100;
            @endphp
            @if($profitMarginPercent < 15)
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h4 class="text-yellow-800 dark:text-yellow-300 font-semibold">Low Profit Margin Alert</h4>
                            <p class="text-yellow-700 dark:text-yellow-400 text-sm mt-1">
                                Your profit margin is {{ number_format($profitMarginPercent, 1) }}%. Consider reviewing your costs or adjusting prices to improve profitability. Target: 20-30% for healthy business.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($profitMarginPercent >= 30)
                <div class="bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <h4 class="text-green-800 dark:text-green-300 font-semibold">Excellent Performance!</h4>
                            <p class="text-green-700 dark:text-green-400 text-sm mt-1">
                                Your profit margin is {{ number_format($profitMarginPercent, 1) }}% - Great work! Keep maintaining quality and efficiency.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        <!-- Recent Transactions -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Recent Transactions
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Last 20 orders</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaction ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentTransactions as $transaction)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono text-gray-900 dark:text-white">{{ $transaction['transaction_id'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">#{{ $transaction['order_id'] }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">৳{{ number_format($transaction['amount'], 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $transaction['payment_method'] === 'BKASH' ? 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200' : '' }}
                                        {{ $transaction['payment_method'] === 'NAGAD' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                        {{ $transaction['payment_method'] === 'COD' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ !in_array($transaction['payment_method'], ['BKASH', 'NAGAD', 'COD']) ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                        {{ $transaction['payment_method'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                                        {{ $transaction['status'] === 'Delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $transaction['status'] === 'Shipped' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                        {{ $transaction['status'] === 'Processing' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                        {{ $transaction['status'] === 'Confirmed' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200' : '' }}">
                                        {{ $transaction['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No transactions found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
