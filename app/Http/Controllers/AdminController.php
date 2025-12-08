<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Order;
use App\Models\User;
use App\Mail\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $totalProducts = Product::count();
        $totalOrders = Order::where('status', '!=', 'pending')->where('status', '!=', 'cancelled')->count();
        $totalCustomers = \App\Models\User::where('email', '!=', 'ifti3061@gmail.com')->count();
        $totalRevenue = Order::where('status', '!=', 'pending')->where('status', '!=', 'cancelled')->sum('total');
        
        $recentOrders = Order::with(['user', 'items'])->latest()->take(5)->get();
        $recentProducts = Product::latest()->take(5)->get();
        
        return view('admin.dashboard', [
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'recentOrders' => $recentOrders,
            'recentProducts' => $recentProducts,
        ]);
    }

    public function orders(Request $request): View
    {
        $status = $request->query('status', '');
        
        $query = Order::with(['user', 'items.product']);
        
        if ($status && in_array($status, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $query->where('status', $status);
        }
        
        $orders = $query->latest()->get();
        
        return view('admin.orders', [
            'orders' => $orders,
            'selectedStatus' => $status,
        ]);
    }

    public function users(): View
    {
        $users = User::latest()->paginate(20);

        return view('admin.users', [
            'users' => $users,
        ]);
    }

    public function featuredProducts(): View
    {
        $featuredProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->latest()
            ->get();
        
        $allProducts = Product::where('is_active', true)
            ->latest()
            ->get();

        return view('admin.featured-products', [
            'featuredProducts' => $featuredProducts,
            'allProducts' => $allProducts,
        ]);
    }

    public function updateFeaturedProducts(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'featured_product_ids' => 'nullable|array',
            'featured_product_ids.*' => 'exists:products,id',
        ]);

        // Remove featured status from all products
        Product::query()->update(['is_featured' => false]);

        // Set featured status for selected products
        if (!empty($validated['featured_product_ids'])) {
            Product::whereIn('id', $validated['featured_product_ids'])
                ->update(['is_featured' => true]);
        }

        return redirect()->route('admin.featured-products')
            ->with('success', 'Featured products updated successfully!');
    }

    public function exportOrders(Request $request)
    {
        $status = $request->query('status', '');
        
        $query = Order::with(['user', 'items']);
        
        if ($status && in_array($status, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $query->where('status', $status);
        }
        
        $orders = $query->latest()->get();
        
        $filename = 'orders_' . date('Y-m-d_His') . ($status ? '_' . $status : '') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 to ensure Excel opens it correctly
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Order ID',
                'Order Date',
                'Status',
                'Customer Name',
                'Email',
                'Phone',
                'Address',
                'User ID',
                'Payment Method',
                'Transaction Number',
                'Sending Number',
                'Subtotal',
                'Discount',
                'Total',
                'Product Name',
                'Product Price',
                'Quantity',
                'Item Subtotal',
                'Notes',
                'Terms Accepted',
                'Save Info'
            ]);
            
            // Data rows
            foreach ($orders as $order) {
                if ($order->items->count() > 0) {
                    foreach ($order->items as $item) {
                        fputcsv($file, [
                            $order->id,
                            $order->created_at->format('Y-m-d H:i:s'),
                            ucfirst($order->status),
                            $order->name,
                            $order->email,
                            $order->phone,
                            $order->address,
                            $order->user_id ?? 'Guest',
                            $order->payment_method,
                            $order->transaction_number,
                            $order->sending_number,
                            number_format($order->subtotal, 2),
                            number_format($order->discount, 2),
                            number_format($order->total, 2),
                            $item->product_name,
                            number_format($item->price, 2),
                            $item->quantity,
                            number_format($item->subtotal, 2),
                            $order->notes ?? '',
                            $order->terms_accepted ? 'Yes' : 'No',
                            $order->save_info ? 'Yes' : 'No'
                        ]);
                    }
                } else {
                    // If order has no items, still export order info
                    fputcsv($file, [
                        $order->id,
                        $order->created_at->format('Y-m-d H:i:s'),
                        ucfirst($order->status),
                        $order->name,
                        $order->email,
                        $order->phone,
                        $order->address,
                        $order->user_id ?? 'Guest',
                        $order->payment_method,
                        $order->transaction_number,
                        $order->sending_number,
                        number_format($order->subtotal, 2),
                        number_format($order->discount, 2),
                        number_format($order->total, 2),
                        'N/A',
                        '0.00',
                        '0',
                        '0.00',
                        $order->notes ?? '',
                        $order->terms_accepted ? 'Yes' : 'No',
                        $order->save_info ? 'Yes' : 'No'
                    ]);
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function updateOrderStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Send email notification if status changed and order has email
        if ($oldStatus !== $validated['status'] && $order->email) {
            try {
                // Reload order with items relationship for email
                $order->refresh();
                $order->load('items');
                Mail::to($order->email)->send(new OrderStatusUpdated($order, $oldStatus));
            } catch (\Exception $e) {
                // Log error but don't fail the status update
                \Log::error('Failed to send order status email: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                    'email' => $order->email,
                ]);
            }
        }

        return redirect()->route('admin.orders')->with('success', 'Order status updated successfully!');
    }

    public function products(Request $request): View
    {
        $category = $request->query('category', '');
        
        $query = Product::query();
        
        if ($category && in_array($category, ['figures', 'knives', 'stickers'])) {
            $query->where('category', $category);
        }
        
        $products = $query->latest()->get();
        
        $categories = [
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
        ];
        
        return view('admin.products', [
            'products' => $products,
            'selectedCategory' => $category,
            'categories' => $categories,
        ]);
    }

    public function productCreate(): View
    {
        $categories = [
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
        ];
        
        return view('admin.product-create', compact('categories'));
    }

    public function productStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:figures,knives,stickers',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'rating' => 'nullable|integer|min:0|max:5',
            'reviews' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $uploadedPaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $uploadedPaths[] = [
                    'path' => $file->store('products', 'public'),
                    'sort_order' => $index,
                ];
            }
        }

        if (!empty($uploadedPaths)) {
            $validated['image'] = $uploadedPaths[0]['path'];
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['rating'] = $validated['rating'] ?? 0;
        $validated['reviews'] = $validated['reviews'] ?? 0;

        $product = Product::create($validated);

        if (!empty($uploadedPaths)) {
            foreach ($uploadedPaths as $pathData) {
                $product->images()->create($pathData);
            }
        }

        return redirect()->route('admin.products')->with('success', 'Product created successfully!');
    }

    public function productEdit(Product $product): View
    {
        $categories = [
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
        ];
        
        return view('admin.product-edit', compact('product', 'categories'));
    }

    public function productUpdate(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'category' => 'required|in:figures,knives,stickers',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer',
            'rating' => 'nullable|integer|min:0|max:5',
            'reviews' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Delete selected images
        $deleteIds = $request->input('delete_images', []);
        if (!empty($deleteIds)) {
            $imagesToDelete = $product->images()->whereIn('id', $deleteIds)->get();
            foreach ($imagesToDelete as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }
        }

        $newImagePaths = [];
        if ($request->hasFile('images')) {
            $existingCount = $product->images()->count();
            foreach ($request->file('images') as $idx => $file) {
                $newImagePaths[] = [
                    'path' => $file->store('products', 'public'),
                    'sort_order' => $existingCount + $idx,
                ];
            }
        }

        if (!empty($newImagePaths)) {
            $validated['image'] = $newImagePaths[0]['path'] ?? $product->image;
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        $product->update($validated);

        if (!empty($newImagePaths)) {
            foreach ($newImagePaths as $pathData) {
                $product->images()->create($pathData);
            }
        }

        // Ensure primary image set to first available
        $primary = $product->images()->orderBy('sort_order')->first();
        $product->update(['image' => $primary->path ?? null]);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully!');
    }

    public function productDestroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        // Delete additional images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        
        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully!');
    }

    public function visitors(): View
    {
        // Get visitor stats from sessions table
        $totalVisitors = DB::table('sessions')
            ->distinct('ip_address')
            ->count('ip_address');
        
        $todayStart = now()->startOfDay()->timestamp;
        $todayVisitors = DB::table('sessions')
            ->where('last_activity', '>=', $todayStart)
            ->distinct('ip_address')
            ->count('ip_address');
        
        $weekVisitors = DB::table('sessions')
            ->where('last_activity', '>=', now()->subWeek()->timestamp)
            ->distinct('ip_address')
            ->count('ip_address');
        
        $monthVisitors = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMonth()->timestamp)
            ->distinct('ip_address')
            ->count('ip_address');
        
        // Get recent sessions with IP and user agent info
        $recentSessions = DB::table('sessions')
            ->select('ip_address', 'user_agent', 'last_activity')
            ->orderBy('last_activity', 'desc')
            ->take(50)
            ->get()
            ->map(function ($session) {
                $deviceInfo = $this->parseUserAgent($session->user_agent ?? '');
                return [
                    'ip_address' => $session->ip_address ?? 'Unknown',
                    'user_agent' => $session->user_agent ?? 'Unknown',
                    'device_type' => $deviceInfo['type'],
                    'browser' => $deviceInfo['browser'],
                    'os' => $deviceInfo['os'],
                    'date_time' => date('Y-m-d H:i:s', $session->last_activity),
                ];
            });
        
        return view('admin.visitors', [
            'totalVisitors' => $totalVisitors,
            'todayVisitors' => $todayVisitors,
            'weekVisitors' => $weekVisitors,
            'monthVisitors' => $monthVisitors,
            'recentSessions' => $recentSessions,
        ]);
    }

    private function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'type' => 'Unknown',
                'browser' => 'Unknown',
                'os' => 'Unknown',
            ];
        }
        
        // Determine device type
        $deviceType = 'Desktop';
        if (stripos($userAgent, 'Mobile') !== false || stripos($userAgent, 'Android') !== false || stripos($userAgent, 'iPhone') !== false) {
            $deviceType = 'Mobile';
        } elseif (stripos($userAgent, 'Tablet') !== false || stripos($userAgent, 'iPad') !== false) {
            $deviceType = 'Tablet';
        }
        
        // Determine browser
        $browser = 'Unknown';
        if (stripos($userAgent, 'Chrome') !== false && stripos($userAgent, 'Edg') === false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false && stripos($userAgent, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($userAgent, 'Opera') !== false || stripos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        }
        
        // Determine OS
        $os = 'Unknown';
        if (stripos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($userAgent, 'Mac OS X') !== false || stripos($userAgent, 'Macintosh') !== false) {
            $os = 'macOS';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        }
        
        return [
            'type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    public function financial(): View
    {
        // Total Revenue (excluding pending and cancelled orders)
        $totalRevenue = Order::where('status', '!=', 'pending')->where('status', '!=', 'cancelled')->sum('total');
        
        // This Month Revenue (excluding pending and cancelled orders)
        $thisMonthRevenue = Order::where('status', '!=', 'pending')
            ->where('status', '!=', 'cancelled')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
        
        // Total Orders (excluding pending and cancelled)
        $totalOrders = Order::where('status', '!=', 'pending')->where('status', '!=', 'cancelled')->count();
        
        // Average Order Value
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        // Recent Transactions (last 20 orders excluding pending and cancelled)
        $recentTransactions = Order::where('status', '!=', 'pending')
            ->where('status', '!=', 'cancelled')
            ->with(['user'])
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($order) {
                return [
                    'transaction_id' => $order->transaction_number ?? 'N/A',
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'payment_method' => strtoupper($order->payment_method ?? 'N/A'),
                    'status' => ucfirst($order->status),
                    'date' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            });
        
        // Revenue by month for chart (last 6 months - excluding pending and cancelled)
        $revenueByMonth = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenueByMonth[] = [
                'month' => $month->format('M Y'),
                'revenue' => Order::where('status', '!=', 'pending')
                    ->where('status', '!=', 'cancelled')
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->sum('total'),
            ];
        }
        
        return view('admin.financial', [
            'totalRevenue' => $totalRevenue,
            'thisMonthRevenue' => $thisMonthRevenue,
            'totalOrders' => $totalOrders,
            'averageOrder' => $averageOrder,
            'recentTransactions' => $recentTransactions,
            'revenueByMonth' => $revenueByMonth,
        ]);
    }
}

