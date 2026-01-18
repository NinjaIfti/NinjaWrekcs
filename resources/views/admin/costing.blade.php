<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Costing & Expenses Management') }}
        </h2>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Product Costs (COGS)</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">৳{{ number_format($totalProductCosts, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Operational Expenses</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">৳{{ number_format($totalExpenses, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $totalLosses = ($expensesByCategory['damaged'] ?? 0) + ($expensesByCategory['returned'] ?? 0) + ($expensesByCategory['lost'] ?? 0);
            @endphp

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-2 {{ $totalLosses > 0 ? 'border-red-300 dark:border-red-700' : 'border-transparent' }}">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Losses & Damages</p>
                            <p class="text-2xl font-semibold text-red-600 dark:text-red-400">৳{{ number_format($totalLosses, 2) }}</p>
                            @if($totalLosses > 0)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $totalExpenses > 0 ? number_format(($totalLosses / ($totalProductCosts + $totalExpenses)) * 100, 1) : 0 }}% of total costs</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Costs</p>
                            <p class="text-2xl font-semibold text-gray-900 dark:text-white">৳{{ number_format($totalProductCosts + $totalExpenses, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loss Alert (if high) -->
        @if($totalLosses > 0 && ($totalProductCosts + $totalExpenses) > 0 && (($totalLosses / ($totalProductCosts + $totalExpenses)) * 100) > 5)
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h4 class="text-red-800 dark:text-red-300 font-semibold">High Loss Rate Detected!</h4>
                        <p class="text-red-700 dark:text-red-400 text-sm mt-1">
                            Your losses are <strong>{{ number_format(($totalLosses / ($totalProductCosts + $totalExpenses)) * 100, 1) }}%</strong> of total costs. 
                            This is impacting your profitability. Consider improving packaging, switching couriers, or reviewing product quality.
                        </p>
                        <p class="text-red-600 dark:text-red-500 text-xs mt-2">
                            💡 <strong>Tip:</strong> Keep losses under 5% for healthy profit margins. Review the "Losses & Damages" section below for details.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Add New Expense -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add New Expense</h3>
                <form action="{{ route('admin.expenses.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category *</label>
                        <select name="category" required class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md">
                            <optgroup label="Operational Costs">
                                <option value="shipping">Shipping Cost</option>
                                <option value="ads">Advertisement</option>
                                <option value="courier">Courier Charges</option>
                                <option value="packaging">Packaging Cost</option>
                            </optgroup>
                            <optgroup label="Losses & Damages">
                                <option value="damaged">Damaged/Broken Items</option>
                                <option value="returned">Customer Returns</option>
                                <option value="lost">Lost in Transit</option>
                            </optgroup>
                            <optgroup label="Other">
                                <option value="other">Other Expenses</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description *</label>
                        <input type="text" name="description" required placeholder="e.g., Facebook ads campaign" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount (৳) *</label>
                        <input type="number" name="amount" step="0.01" required placeholder="0.00" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date *</label>
                        <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes (Optional)</label>
                        <textarea name="notes" rows="2" placeholder="Additional details..." class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Add Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expense Breakdown by Category -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Expenses by Category</h3>
                
                <!-- Operational Costs -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Operational Costs</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $operationalCategories = ['shipping' => 'Shipping', 'ads' => 'Ads', 'courier' => 'Courier', 'packaging' => 'Packaging'];
                        @endphp
                        @foreach($operationalCategories as $key => $label)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center bg-blue-50 dark:bg-blue-900/10">
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">৳{{ number_format($expensesByCategory[$key] ?? 0, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Losses & Damages -->
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Losses & Damages</h4>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @php
                            $lossCategories = ['damaged' => 'Damaged Items', 'returned' => 'Returns', 'lost' => 'Lost Items'];
                            $totalLosses = 0;
                            foreach($lossCategories as $key => $label) {
                                $totalLosses += $expensesByCategory[$key] ?? 0;
                            }
                        @endphp
                        @foreach($lossCategories as $key => $label)
                            <div class="border border-red-200 dark:border-red-700 rounded-lg p-4 text-center bg-red-50 dark:bg-red-900/10">
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                                <p class="text-lg font-bold text-red-600 dark:text-red-400">৳{{ number_format($expensesByCategory[$key] ?? 0, 2) }}</p>
                            </div>
                        @endforeach
                    </div>
                    @if($totalLosses > 0)
                        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-700 rounded-lg">
                            <p class="text-sm text-red-700 dark:text-red-300">
                                <strong>Total Losses:</strong> ৳{{ number_format($totalLosses, 2) }} 
                                <span class="text-xs">({{ $totalExpenses > 0 ? number_format(($totalLosses / $totalExpenses) * 100, 1) : 0 }}% of total expenses)</span>
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Other Expenses -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Other Expenses</h4>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 text-center bg-gray-50 dark:bg-gray-700/10">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Miscellaneous</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">৳{{ number_format($expensesByCategory['other'] ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Expenses -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Expenses</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Description</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($expenses as $expense)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $expense->expense_date->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $expense->category === 'shipping' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                            {{ $expense->category === 'ads' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                            {{ $expense->category === 'courier' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                            {{ $expense->category === 'packaging' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                            {{ $expense->category === 'damaged' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                            {{ $expense->category === 'returned' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                            {{ $expense->category === 'lost' ? 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200' : '' }}
                                            {{ $expense->category === 'other' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                            @if($expense->category === 'damaged')
                                                Damaged
                                            @elseif($expense->category === 'returned')
                                                Returned
                                            @elseif($expense->category === 'lost')
                                                Lost
                                            @else
                                                {{ ucfirst($expense->category) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $expense->description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-right text-gray-900 dark:text-white">৳{{ number_format($expense->amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <form action="{{ route('admin.expenses.delete', $expense) }}" method="POST" onsubmit="return confirm('Delete this expense?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No expenses recorded yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Product Cost Pricing -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Product Cost Pricing</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Set the cost price for each product to track profit margins accurately.</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Selling Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cost Price</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Profit/Unit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($products as $product)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $product['name'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">৳{{ number_format($product['selling_price'], 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                        <form action="{{ route('admin.product.update-cost', $product['id']) }}" method="POST" class="inline-flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="number" name="cost_price" step="0.01" value="{{ $product['cost_price'] }}" class="w-24 px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded text-right text-sm">
                                            <button type="submit" class="text-blue-600 hover:text-blue-700 text-xs">Save</button>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $product['profit_per_unit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        ৳{{ number_format($product['profit_per_unit'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold {{ $product['profit_per_unit'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $product['selling_price'] > 0 ? number_format(($product['profit_per_unit'] / $product['selling_price']) * 100, 1) : 0 }}%
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
