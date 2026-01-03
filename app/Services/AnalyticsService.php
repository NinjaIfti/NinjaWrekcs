<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Visitor;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * Get sales report for date range
     */
    public static function getSalesReport(string $period = 'daily', ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $cacheKey = "sales_report_{$period}_" . ($startDate?->format('Y-m-d') ?? 'all') . '_' . ($endDate?->format('Y-m-d') ?? 'all');
        
        return Cache::remember($cacheKey, 300, function () use ($period, $startDate, $endDate) {
            $query = Order::where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled');

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $groupBy = match($period) {
                'daily' => "DATE(created_at)",
                'weekly' => "YEARWEEK(created_at, 1)",
                'monthly' => "DATE_FORMAT(created_at, '%Y-%m')",
                default => "DATE(created_at)",
            };

            $rawData = $query->select(
                DB::raw("{$groupBy} as period"),
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(total) as total_revenue'),
                DB::raw('AVG(total) as avg_order_value')
            )
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->get();

            $totalOrders = $rawData->sum('total_orders');
            $totalRevenue = $rawData->sum('total_revenue');

            // Transform data to match view expectations
            $formattedData = $rawData->map(function ($item) use ($period) {
                $date = match($period) {
                    'daily' => Carbon::parse($item->period)->format('M d, Y'),
                    'weekly' => 'Week ' . substr($item->period, -2) . ', ' . substr($item->period, 0, 4),
                    'monthly' => Carbon::parse($item->period . '-01')->format('M Y'),
                    default => $item->period,
                };

                return [
                    'date' => $date,
                    'orders' => $item->total_orders,
                    'revenue' => $item->total_revenue,
                    'average' => $item->avg_order_value,
                ];
            });

            return [
                'data' => $formattedData,
                'summary' => [
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
                ],
            ];
        });
    }

    /**
     * Get product performance analytics
     */
    public static function getProductPerformance(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $cacheKey = "product_performance_" . ($startDate?->format('Y-m-d') ?? 'all') . '_' . ($endDate?->format('Y-m-d') ?? 'all');
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $query = OrderItem::with('product')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', '!=', 'pending')
                ->where('orders.status', '!=', 'cancelled');

            if ($startDate) {
                $query->whereDate('orders.created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('orders.created_at', '<=', $endDate);
            }

            $topProducts = $query->select(
                'order_items.product_id',
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('AVG(order_items.price) as avg_price')
            )
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderBy('total_revenue', 'desc')
            ->limit(20)
            ->get();

            // Category performance
            $categoryPerformance = DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', '!=', 'pending')
                ->where('orders.status', '!=', 'cancelled')
                ->when($startDate, fn($q) => $q->whereDate('orders.created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('orders.created_at', '<=', $endDate))
                ->select(
                    'products.category',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(order_items.quantity) as products_sold'),
                    DB::raw('SUM(order_items.subtotal) as revenue')
                )
                ->groupBy('products.category')
                ->get();

            return [
                'top_products' => $topProducts,
                'category_performance' => $categoryPerformance,
            ];
        });
    }

    /**
     * Get customer analytics
     */
    public static function getCustomerAnalytics(): array
    {
        return Cache::remember('customer_analytics', 300, function () {
            // Top customers by revenue
            $topCustomers = DB::table('orders')
                ->join('users', 'orders.user_id', '=', 'users.id')
                ->where('users.email', '!=', 'ifti3061@gmail.com')
                ->where('orders.status', '!=', 'pending')
                ->where('orders.status', '!=', 'cancelled')
                ->select(
                    'users.id as user_id',
                    'users.name',
                    'users.email',
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(orders.total) as total_spent'),
                    DB::raw('AVG(orders.total) as avg_order_value'),
                    DB::raw('MAX(orders.created_at) as last_order_date')
                )
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('total_spent', 'desc')
                ->limit(10)
                ->get();

            // New customers trend
            $newCustomersTrend = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('email', '!=', 'ifti3061@gmail.com')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

            // Customer retention
            $totalCustomers = User::where('email', '!=', 'ifti3061@gmail.com')->count();
            $returningCustomers = User::whereHas('orders', function($q) {
                $q->where('status', '!=', 'pending')
                  ->where('status', '!=', 'cancelled');
            }, '>=', 2)->count();

            // New customers count (last 30 days)
            $newCustomers = User::where('email', '!=', 'ifti3061@gmail.com')
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->count();

            // Average lifetime value
            $avgLifetimeValue = $totalCustomers > 0
                ? Order::where('status', '!=', 'pending')
                    ->where('status', '!=', 'cancelled')
                    ->sum('total') / $totalCustomers
                : 0;

            return [
                'top_customers' => $topCustomers,
                'new_customers_trend' => $newCustomersTrend,
                'new_customers' => $newCustomers,
                'total_customers' => $totalCustomers,
                'returning_customers' => $returningCustomers,
                'retention_rate' => $totalCustomers > 0 ? round(($returningCustomers / $totalCustomers) * 100, 2) : 0,
                'average_lifetime_value' => $avgLifetimeValue,
            ];
        });
    }

    /**
     * Get dashboard summary
     */
    public static function getDashboardSummary(): array
    {
        return Cache::remember('dashboard_summary', 300, function () {
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();

            // Today's stats
            $todayOrders = Order::whereDate('created_at', $today)
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->count();
            
            $todayRevenue = Order::whereDate('created_at', $today)
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->sum('total');

            // This month stats
            $thisMonthOrders = Order::where('created_at', '>=', $thisMonth)
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->count();
            
            $thisMonthRevenue = Order::where('created_at', '>=', $thisMonth)
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->sum('total');

            // Last month stats for comparison
            $lastMonthOrders = Order::whereBetween('created_at', [$lastMonth, $thisMonth])
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->count();
            
            $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonth, $thisMonth])
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->sum('total');

            // Calculate growth
            $orderGrowth = $lastMonthOrders > 0 
                ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 2) 
                : 0;
            
            $revenueGrowth = $lastMonthRevenue > 0 
                ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2) 
                : 0;

            // Pending orders
            $pendingOrders = Order::where('status', 'pending')->count();

            // Low stock products
            $lowStockProducts = Product::where('is_active', true)
                ->where('quantity', '<=', 5)
                ->where('quantity', '>', 0)
                ->select('id', 'name', 'category', 'quantity')
                ->get();

            return [
                'today' => [
                    'orders' => $todayOrders,
                    'revenue' => $todayRevenue,
                ],
                'this_month' => [
                    'orders' => $thisMonthOrders,
                    'revenue' => $thisMonthRevenue,
                    'order_growth' => $orderGrowth,
                    'revenue_growth' => $revenueGrowth,
                    'growth' => $revenueGrowth,
                ],
                'pending_orders' => $pendingOrders,
                'low_stock_products' => $lowStockProducts,
            ];
        });
    }

    /**
     * Clear analytics cache
     */
    public static function clearCache(): void
    {
        Cache::flush();
    }
}








