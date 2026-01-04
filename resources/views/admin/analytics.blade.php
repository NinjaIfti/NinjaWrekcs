<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Analytics & Reports') }}
        </h2>
    </x-slot>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-white glitch-text">📊 Analytics & Reports</h1>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.analytics.clear-cache') }}" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 rounded-lg hover:bg-yellow-500/20 transition-colors text-sm">
                    🔄 Clear Cache
                </button>
            </form>
            <a href="{{ route('admin.analytics.export', ['period' => $period, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-4 py-2 bg-green-500/10 border border-green-500/30 text-green-400 rounded-lg hover:bg-green-500/20 transition-colors text-sm">
                📥 Export to Excel
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Dashboard Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-violet-500/10 to-purple-500/10 border border-violet-500/30 rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm">Today's Revenue</h3>
                <span class="text-2xl">💰</span>
            </div>
            <p class="text-2xl font-bold text-white">৳{{ number_format($dashboardSummary['today']['revenue'], 2) }}</p>
            <p class="text-sm text-gray-400 mt-1">{{ $dashboardSummary['today']['orders'] }} orders</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500/10 to-cyan-500/10 border border-blue-500/30 rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm">This Month</h3>
                <span class="text-2xl">📈</span>
            </div>
            <p class="text-2xl font-bold text-white">৳{{ number_format($dashboardSummary['this_month']['revenue'], 2) }}</p>
            <p class="text-sm {{ $dashboardSummary['this_month']['growth'] >= 0 ? 'text-green-400' : 'text-red-400' }} mt-1">
                {{ $dashboardSummary['this_month']['growth'] >= 0 ? '↑' : '↓' }} {{ abs($dashboardSummary['this_month']['growth']) }}% vs last month
            </p>
        </div>

        <div class="bg-gradient-to-br from-orange-500/10 to-red-500/10 border border-orange-500/30 rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm">Pending Orders</h3>
                <span class="text-2xl">⏳</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $dashboardSummary['pending_orders'] }}</p>
            <p class="text-sm text-gray-400 mt-1">Need attention</p>
        </div>

        <div class="bg-gradient-to-br from-red-500/10 to-pink-500/10 border border-red-500/30 rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-gray-400 text-sm">Low Stock</h3>
                <span class="text-2xl">📦</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ count($dashboardSummary['low_stock_products']) }}</p>
            <p class="text-sm text-gray-400 mt-1">Products &lt; 5 units</p>
        </div>
    </div>

    <!-- Sales Report -->
    <div class="bg-black/40 backdrop-blur-sm border border-violet-500/30 rounded-lg p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h2 class="text-xl font-bold text-white">📊 Sales Report</h2>
            
            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.analytics') }}" class="flex flex-wrap gap-3">
                <select name="period" class="px-4 py-2 bg-gray-900 border border-violet-500/30 rounded-lg text-white text-sm focus:ring-2 focus:ring-violet-500">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $period === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
                
                <input type="date" name="start_date" value="{{ $startDate }}" placeholder="Start Date" class="px-4 py-2 bg-gray-900 border border-violet-500/30 rounded-lg text-white text-sm focus:ring-2 focus:ring-violet-500">
                
                <input type="date" name="end_date" value="{{ $endDate }}" placeholder="End Date" class="px-4 py-2 bg-gray-900 border border-violet-500/30 rounded-lg text-white text-sm focus:ring-2 focus:ring-violet-500">
                
                <button type="submit" class="px-6 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors text-sm font-semibold">
                    Apply
                </button>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-violet-500/5 border border-violet-500/20 rounded-lg p-4">
                <p class="text-gray-400 text-sm mb-1">Total Orders</p>
                <p class="text-2xl font-bold text-violet-400">{{ $salesReport['summary']['total_orders'] }}</p>
            </div>
            <div class="bg-violet-500/5 border border-violet-500/20 rounded-lg p-4">
                <p class="text-gray-400 text-sm mb-1">Total Revenue</p>
                <p class="text-2xl font-bold text-violet-400">৳{{ number_format($salesReport['summary']['total_revenue'], 2) }}</p>
            </div>
            <div class="bg-violet-500/5 border border-violet-500/20 rounded-lg p-4">
                <p class="text-gray-400 text-sm mb-1">Average Order Value</p>
                <p class="text-2xl font-bold text-violet-400">৳{{ number_format($salesReport['summary']['average_order_value'], 2) }}</p>
            </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-violet-500/30">
                        <th class="text-left py-3 px-4 text-gray-400 font-semibold">Date</th>
                        <th class="text-right py-3 px-4 text-gray-400 font-semibold">Orders</th>
                        <th class="text-right py-3 px-4 text-gray-400 font-semibold">Revenue</th>
                        <th class="text-right py-3 px-4 text-gray-400 font-semibold">Avg Order</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($salesReport['data'] as $item)
                        <tr class="border-b border-violet-500/10 hover:bg-violet-500/5 transition-colors">
                            <td class="py-3 px-4 text-white">{{ $item['date'] }}</td>
                            <td class="py-3 px-4 text-right text-white">{{ $item['orders'] }}</td>
                            <td class="py-3 px-4 text-right text-green-400">৳{{ number_format($item['revenue'], 2) }}</td>
                            <td class="py-3 px-4 text-right text-violet-400">৳{{ number_format($item['average'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400">No data available for the selected period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Product Performance & Customer Analytics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Selling Products -->
        <div class="bg-black/40 backdrop-blur-sm border border-violet-500/30 rounded-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">🏆 Top Selling Products</h2>
            <div class="space-y-3">
                @forelse($productPerformance['top_products'] as $product)
                    <div class="flex items-center justify-between p-3 bg-violet-500/5 border border-violet-500/20 rounded-lg">
                        <div class="flex-1">
                            <p class="text-white font-semibold">{{ $product->name }}</p>
                            <p class="text-sm text-gray-400">{{ $product->total_sold }} units sold</p>
                        </div>
                        <div class="text-right">
                            <p class="text-green-400 font-bold">৳{{ number_format($product->total_revenue, 2) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">No product sales yet</p>
                @endforelse
            </div>
        </div>

        <!-- Category Performance -->
        <div class="bg-black/40 backdrop-blur-sm border border-violet-500/30 rounded-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">📂 Category Performance</h2>
            <div class="space-y-3">
                @forelse($productPerformance['category_performance'] as $category)
                    <div class="p-3 bg-violet-500/5 border border-violet-500/20 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-white font-semibold">{{ $category->category }}</p>
                            <p class="text-green-400 font-bold">৳{{ number_format($category->revenue, 2) }}</p>
                        </div>
                        <div class="flex justify-between text-sm text-gray-400">
                            <span>{{ $category->products_sold }} units</span>
                            <span>{{ $category->order_count }} orders</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">No category data available</p>
                @endforelse
            </div>
        </div>

        <!-- Top Customers -->
        <div class="bg-black/40 backdrop-blur-sm border border-violet-500/30 rounded-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">⭐ Top Customers</h2>
            <div class="space-y-3">
                @forelse($customerAnalytics['top_customers'] as $customer)
                    <div class="flex items-center justify-between p-3 bg-violet-500/5 border border-violet-500/20 rounded-lg">
                        <div class="flex-1">
                            <p class="text-white font-semibold">{{ $customer->name }}</p>
                            <p class="text-sm text-gray-400">{{ $customer->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-green-400 font-bold">৳{{ number_format($customer->total_spent, 2) }}</p>
                            <p class="text-sm text-gray-400">{{ $customer->order_count }} orders</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">No customer data available</p>
                @endforelse
            </div>
        </div>

        <!-- Customer Insights -->
        <div class="bg-black/40 backdrop-blur-sm border border-violet-500/30 rounded-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">👥 Customer Insights</h2>
            <div class="space-y-4">
                <div class="p-4 bg-blue-500/5 border border-blue-500/20 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">New Customers (30 days)</span>
                        <span class="text-2xl font-bold text-blue-400">{{ $customerAnalytics['new_customers'] }}</span>
                    </div>
                </div>
                <div class="p-4 bg-green-500/5 border border-green-500/20 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Retention Rate</span>
                        <span class="text-2xl font-bold text-green-400">{{ number_format($customerAnalytics['retention_rate'], 1) }}%</span>
                    </div>
                </div>
                <div class="p-4 bg-purple-500/5 border border-purple-500/20 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Avg Lifetime Value</span>
                        <span class="text-2xl font-bold text-purple-400">৳{{ number_format($customerAnalytics['average_lifetime_value'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if(count($dashboardSummary['low_stock_products']) > 0)
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-6">
            <h2 class="text-xl font-bold text-red-400 mb-4">⚠️ Low Stock Alert</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($dashboardSummary['low_stock_products'] as $product)
                    <div class="flex items-center justify-between p-3 bg-black/40 border border-red-500/20 rounded-lg">
                        <div>
                            <p class="text-white font-semibold">{{ $product->name }}</p>
                            <p class="text-sm text-gray-400">{{ $product->category }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-red-400 font-bold text-lg">{{ $product->quantity }}</p>
                            <p class="text-xs text-gray-400">units left</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
</x-admin-layout>










