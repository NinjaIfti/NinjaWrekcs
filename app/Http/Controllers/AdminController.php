<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Visitor;
use App\Models\Coupon;
use App\Models\PopupSetting;
use App\Models\SpecialOffer;
use App\Models\Review;
use App\Models\StockNotification;
use App\Mail\OrderStatusUpdated;
use App\Mail\AdminOrderNotification;
use App\Services\AnalyticsService;
use App\Services\NotificationService;
use App\Services\EmailService;
use App\Services\SmsNetBdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        // Cache dashboard stats for 5 minutes (admin data changes frequently)
        $cacheKey = 'admin_dashboard_stats';
        
        $stats = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            $totalProducts = Product::count();
            $totalOrders = Order::where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where('is_preorder_booking', false)
                ->count();
            $totalCustomers = \App\Models\User::where('email', '!=', 'ifti3061@gmail.com')->count();
            
            // Calculate revenue excluding delivery charges (subtotal - discount)
            // Exclude pre-order bookings from financial calculations
            $orders = Order::where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where('is_preorder_booking', false)
                ->get();
            $totalRevenue = $orders->sum(function($order) {
                return $order->subtotal - $order->discount;
            });
            
            return [
                'totalProducts' => $totalProducts,
                'totalOrders' => $totalOrders,
                'totalCustomers' => $totalCustomers,
                'totalRevenue' => $totalRevenue,
            ];
        });
        
        // Recent data not cached (always fresh)
        $recentOrders = Order::with(['user', 'items'])->latest()->take(5)->get();
        $recentProducts = Product::latest()->take(5)->get();
        
        return view('admin.dashboard', array_merge($stats, [
            'recentOrders' => $recentOrders,
            'recentProducts' => $recentProducts,
        ]));
    }

    public function orders(Request $request): View
    {
        $status = $request->query('status', '');
        $view = $request->query('view', 'active'); // 'active', 'preorder', or 'hidden'

        $query = Order::with(['user', 'items.product']);

        // Filter by view type
        if ($view === 'hidden') {
            // Show deleted orders (regardless of preorder status)
            $query->where('is_deleted', true);
        } elseif ($view === 'preorder') {
            // Show non-deleted pre-order bookings only
            $query->notDeleted()->where('is_preorder_booking', true);
        } else {
            // Show non-deleted, non-preorder orders (regular active orders)
            $query->notDeleted()->where('is_preorder_booking', false);
        }

        if ($status && in_array($status, ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->get();

        return view('admin.orders', [
            'orders' => $orders,
            'selectedStatus' => $status,
            'currentView' => $view,
        ]);
    }

    public function orderCreate(): View
    {
        $products = Product::where('is_active', true)
            ->where('quantity', '>', 0)
            ->orderBy('name')
            ->get();
        
        $coupons = Coupon::where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('admin.order-create', [
            'products' => $products,
            'coupons' => $coupons,
        ]);
    }

    public function orderStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'email' => 'nullable|email|max:255',
            'payment_method' => 'required|in:bkash,nagad,rocket,cod',
            'transaction_number' => 'nullable|string|max:255',
            'sending_number' => 'nullable|string|max:20',
            'coupon_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'delivery_location' => 'required|in:inside_dhaka,outside_dhaka',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Calculate subtotal
            $subtotal = 0;
            $orderItems = [];
            
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                
                // Check stock
                if ($product->quantity < $productData['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Only {$product->quantity} available.");
                }
                
                // Use display_price (includes deals/offers) instead of regular price
                $itemPrice = $product->display_price ?? $product->price;
                $itemSubtotal = $itemPrice * $productData['quantity'];
                $subtotal += $itemSubtotal;
                
                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $productData['quantity'],
                    'subtotal' => $itemSubtotal,
                    'price' => $itemPrice,
                ];
            }

            // Calculate delivery charge
            $deliveryCharge = $validated['delivery_location'] === 'inside_dhaka' ? 80 : 120;
            
            // Apply coupon if provided
            $coupon = null;
            $couponDiscount = 0;
            $totalDiscount = 0;
            
            if (!empty($validated['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();
                if ($coupon && $coupon->isValid()) {
                    // Check minimum order requirement
                    if (!$coupon->minimum_order || $subtotal >= $coupon->minimum_order) {
                        $couponDiscount = $coupon->calculateDiscount($subtotal);
                        $totalDiscount = $couponDiscount;
                    }
                }
            }
            
            $finalTotal = max(0, $subtotal + $deliveryCharge - $totalDiscount);

            // Create order (without user_id - guest order)
            $order = Order::create([
                'user_id' => null,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'coupon_discount' => $couponDiscount,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'delivery_location' => $validated['delivery_location'],
                'delivery_charge' => $deliveryCharge,
                'email' => $validated['email'],
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'total' => $finalTotal,
                'payment_method' => $validated['payment_method'],
                'transaction_number' => $validated['transaction_number'] ?? null,
                'sending_number' => $validated['sending_number'] ?? null,
                'status' => $validated['status'],
                'save_info' => false,
                'terms_accepted' => true,
                'notes' => $validated['notes'],
            ]);
            
            // Increment coupon usage
            if ($coupon) {
                $coupon->incrementUsage();
            }

            // Create order items and update stock
            foreach ($orderItems as $item) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'price' => $item['price'], // Use the deal price if applicable
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Update product quantity
                $item['product']->decrement('quantity', $item['quantity']);
            }

            DB::commit();

            // Send order confirmation email if email provided
            if ($order->email) {
                $order->load('items');
                EmailService::sendWithFallback(
                    new \App\Mail\OrderConfirmation($order),
                    $order->email,
                    'manual order confirmation'
                );
            }

            // Send admin notification email for manual orders
            try {
                $order->load('items');
                Mail::to('ifti3061@gmail.com')->send(new AdminOrderNotification($order));
            } catch (\Exception $e) {
                \Log::error('Failed to send admin order notification email for manual order', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Clear admin dashboard cache when order is created
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('admin_financial_data');

            return redirect()->route('admin.orders')->with('success', 'Manual order created successfully! Order #' . $order->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create order: ' . $e->getMessage());
        }
    }

    public function orderEdit(Order $order): View
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $coupons = Coupon::where('is_active', true)
            ->orderBy('code')
            ->get();

        // Load order relationships
        $order->load(['items.product', 'changes.user']);

        return view('admin.order-edit', [
            'order' => $order,
            'products' => $products,
            'coupons' => $coupons,
        ]);
    }

    public function orderUpdate(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'delivery_location' => 'required|in:inside_dhaka,outside_dhaka',
            'email' => 'nullable|email|max:255',
            'payment_method' => 'required|in:bkash,nagad,rocket,cod',
            'transaction_number' => 'nullable|string|max:255',
            'sending_number' => 'nullable|string|max:20',
            'coupon_code' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
            'tracking_link' => 'nullable|string|max:500',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Track original order items
            $originalItems = $order->items->keyBy('product_id');
            $changes = [];

            // Calculate new subtotal
            $subtotal = 0;
            $newItemsData = [];
            
            foreach ($validated['products'] as $productData) {
                $product = Product::findOrFail($productData['id']);
                $quantity = $productData['quantity'];
                
                // Use display_price (includes deals/offers) instead of regular price
                $itemPrice = $product->display_price ?? $product->price;
                $itemSubtotal = $itemPrice * $quantity;
                $subtotal += $itemSubtotal;
                
                $newItemsData[$product->id] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'price' => $itemPrice,
                ];
            }

            // Detect item changes
            foreach ($newItemsData as $productId => $newItem) {
                if ($originalItems->has($productId)) {
                    // Item exists - check quantity change
                    $originalItem = $originalItems[$productId];
                    if ($originalItem->quantity != $newItem['quantity']) {
                        $changes[] = [
                            'type' => 'item_quantity_changed',
                            'description' => "Changed quantity of '{$newItem['product']->name}' from {$originalItem->quantity} to {$newItem['quantity']}",
                            'old_data' => ['product_id' => $productId, 'quantity' => $originalItem->quantity],
                            'new_data' => ['product_id' => $productId, 'quantity' => $newItem['quantity']],
                        ];
                        
                        // Return old quantity to stock and deduct new quantity
                        $newItem['product']->increment('quantity', $originalItem->quantity);
                        $newItem['product']->decrement('quantity', $newItem['quantity']);
                    }
                } else {
                    // New item added
                    $changes[] = [
                        'type' => 'item_added',
                        'description' => "Added '{$newItem['product']->name}' x{$newItem['quantity']}",
                        'old_data' => null,
                        'new_data' => ['product_id' => $productId, 'quantity' => $newItem['quantity']],
                    ];
                    
                    // Deduct from stock
                    if ($newItem['product']->quantity < $newItem['quantity']) {
                        throw new \Exception("Insufficient stock for {$newItem['product']->name}");
                    }
                    $newItem['product']->decrement('quantity', $newItem['quantity']);
                }
            }

            // Detect removed items
            foreach ($originalItems as $productId => $originalItem) {
                if (!isset($newItemsData[$productId])) {
                    $changes[] = [
                        'type' => 'item_removed',
                        'description' => "Removed '{$originalItem->product_name}' x{$originalItem->quantity}",
                        'old_data' => ['product_id' => $productId, 'quantity' => $originalItem->quantity],
                        'new_data' => null,
                    ];
                    
                    // Return to stock
                    if ($originalItem->product) {
                        $originalItem->product->increment('quantity', $originalItem->quantity);
                    }
                }
            }

            // Calculate delivery charge
            $deliveryCharge = $validated['delivery_location'] === 'inside_dhaka' ? 80 : 120;
            
            // Apply coupon if provided
            $coupon = null;
            $couponDiscount = 0;
            $totalDiscount = 0;
            
            if (!empty($validated['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();
                if ($coupon && $coupon->isValid()) {
                    if (!$coupon->minimum_order || $subtotal >= $coupon->minimum_order) {
                        $couponDiscount = $coupon->calculateDiscount($subtotal);
                        $totalDiscount = $couponDiscount;
                    }
                }
            }
            
            $finalTotal = max(0, $subtotal + $deliveryCharge - $totalDiscount);

            // Delete old order items
            $order->items()->delete();

            // Create new order items
            foreach ($newItemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'price' => $item['price'], // Use deal price if applicable
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Check if order is being cancelled (restore stock if not already cancelled)
            $oldStatus = $order->status;
            $beingCancelled = ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled');
            
            // If being cancelled, we need to restore the NEW items that were just added
            // (The old items were already returned to stock above in the item removal/change logic)
            if ($beingCancelled) {
                foreach ($newItemsData as $item) {
                    $item['product']->increment('quantity', $item['quantity']);
                }
                
                $changes[] = [
                    'type' => 'order_updated',
                    'description' => "Order cancelled - all items returned to stock",
                    'old_data' => ['status' => $oldStatus],
                    'new_data' => ['status' => 'cancelled'],
                ];
            }
            
            // Update order details
            $order->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'delivery_location' => $validated['delivery_location'],
                'delivery_charge' => $deliveryCharge,
                'email' => $validated['email'],
                'payment_method' => $validated['payment_method'],
                'transaction_number' => $validated['transaction_number'],
                'sending_number' => $validated['sending_number'],
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'coupon_discount' => $couponDiscount,
                'subtotal' => $subtotal,
                'discount' => $totalDiscount,
                'total' => $finalTotal,
                'status' => $validated['status'],
                'tracking_link' => $validated['tracking_link'],
                'notes' => $validated['notes'],
            ]);

            // Send email notification if status changed and order has email
            if ($oldStatus !== $validated['status'] && $order->email) {
                // Reload order with items relationship for email
                $order->refresh();
                $order->load('items');
                
                $emailResult = \App\Services\EmailService::sendWithFallback(
                    new \App\Mail\OrderStatusUpdated($order, $oldStatus),
                    $order->email,
                    'order status update'
                );

                // Send in-app notification (only if user exists)
                \App\Services\NotificationService::orderStatusUpdated($order, $oldStatus);
            }

            // Log all changes
            foreach ($changes as $change) {
                \App\Models\OrderChange::create([
                    'order_id' => $order->id,
                    'user_id' => \Auth::id(),
                    'change_type' => $change['type'],
                    'description' => $change['description'],
                    'old_data' => $change['old_data'],
                    'new_data' => $change['new_data'],
                ]);
            }

            DB::commit();

            // Clear admin dashboard cache when order is updated
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('admin_financial_data');

            return redirect()->route('admin.orders')->with('success', 'Order #' . $order->id . ' updated successfully!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
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
            'tracking_link' => 'nullable|string|max:500',
        ]);

        $oldStatus = $order->status;
        
        // Restore stock if order is being cancelled (and wasn't cancelled before)
        if ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            $order->load('items.product');
            
            foreach ($order->items as $item) {
                if ($item->product) {
                    // Return items back to stock
                    $item->product->increment('quantity', $item->quantity);
                }
            }
        }
        
        // Update order status and tracking link
        $updateData = ['status' => $validated['status']];
        
        // Only update tracking link if status is shipped
        if ($validated['status'] === 'shipped' && isset($validated['tracking_link'])) {
            $updateData['tracking_link'] = $validated['tracking_link'];
        }
        
        $order->update($updateData);

        // Send email notification if status changed and order has email
        if ($oldStatus !== $validated['status'] && $order->email) {
            // Reload order with items relationship for email
            $order->refresh();
            $order->load('items');
            
            $emailResult = \App\Services\EmailService::sendWithFallback(
                new OrderStatusUpdated($order, $oldStatus),
                $order->email,
                'order status update'
            );

            // Send in-app notification
            \App\Services\NotificationService::orderStatusUpdated($order, $oldStatus);

            // Show email status in admin notification
            if (!$emailResult['success']) {
                return redirect()->route('admin.orders')
                    ->with('success', 'Order status updated successfully!' . ($validated['status'] === 'cancelled' ? ' Stock has been restored.' : ''))
                    ->with('warning', 'Status updated but email notification could not be sent to customer.');
            }
        }

        $successMessage = 'Order status updated successfully!';
        if ($validated['status'] === 'cancelled' && $oldStatus !== 'cancelled') {
            $successMessage .= ' Stock has been restored.';
        }

        // Clear admin dashboard cache when order status is updated
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
        \Illuminate\Support\Facades\Cache::forget('admin_financial_data');

        return redirect()->route('admin.orders')->with('success', $successMessage);
    }

    public function products(Request $request): View
    {
        $categoryId = $request->query('category_id', '');
        
        $query = Product::with('category', 'images');
        
        // Get the 4 main categories for tabs with product counts
        $mainCategories = \App\Models\Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('order')
            ->with('children')
            ->get()
            ->map(function($category) {
                // Count products in this category and its subcategories
                if ($category->hasChildren()) {
                    $categoryIds = [$category->id];
                    $categoryIds = array_merge($categoryIds, $category->children->pluck('id')->toArray());
                    $category->product_count = Product::whereIn('category_id', $categoryIds)->count();
                } else {
                    $category->product_count = Product::where('category_id', $category->id)->count();
                }
                return $category;
            });
        
        // Filter by category if selected
        $subcategoryId = $request->query('subcategory_id', '');
        
        if ($categoryId) {
            $selectedCategory = \App\Models\Category::with('children')->find($categoryId);
            
            if ($selectedCategory) {
                // If subcategory is selected (for Valorant), filter by subcategory only
                if ($subcategoryId) {
                    $query->where('category_id', $subcategoryId);
                } elseif ($selectedCategory->hasChildren()) {
                    // If it's Valorant (has subcategories), include products from parent and all subcategories
                    $categoryIds = [$selectedCategory->id];
                    $categoryIds = array_merge($categoryIds, $selectedCategory->children->pluck('id')->toArray());
                    $query->whereIn('category_id', $categoryIds);
                } else {
                    // For other categories (CS:GO, Toys, Pre-order), just filter by category_id
                    $query->where('category_id', $categoryId);
                }
            }
        }
        
        $products = $query->latest()->get();
        
        // Get total product count for "All Products" tab
        $totalProductCount = Product::count();
        
        return view('admin.products', [
            'products' => $products,
            'selectedCategoryId' => $categoryId,
            'selectedSubcategoryId' => $subcategoryId,
            'mainCategories' => $mainCategories,
            'totalProductCount' => $totalProductCount,
        ]);
    }

    public function productCreate(): View
    {
        $categories = \App\Models\Category::with('children')
            ->whereNull('parent_id')
            ->active()
            ->orderBy('order')
            ->get();
        
        return view('admin.product-create', compact('categories'));
    }

    public function productStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0',
            'offer_starts_at' => 'nullable|date',
            'offer_ends_at' => 'nullable|date|after:offer_starts_at',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|in:figures,knives,stickers',
            'is_preorder' => 'boolean',
            'is_upcoming' => 'boolean',
            'price_tba' => 'boolean',
            'is_bookable' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'rating' => 'nullable|integer|min:0|max:5',
            'reviews' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_limited_edition' => 'boolean',
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
        $validated['is_new'] = $request->has('is_new');
        $validated['is_bestseller'] = $request->has('is_bestseller');
        $validated['is_limited_edition'] = $request->has('is_limited_edition');
        $validated['is_preorder'] = $request->has('is_preorder');
        $validated['is_upcoming'] = $request->has('is_upcoming');
        $validated['price_tba'] = $request->has('price_tba');
        $validated['is_bookable'] = $request->has('is_bookable');
        $validated['rating'] = $validated['rating'] ?? 0;
        $validated['reviews'] = $validated['reviews'] ?? 0;

        $product = Product::create($validated);

        if (!empty($uploadedPaths)) {
            foreach ($uploadedPaths as $pathData) {
                $product->images()->create($pathData);
            }
        }

        // Clear relevant caches
        \Illuminate\Support\Facades\Cache::forget('homepage_data');
        \Illuminate\Support\Facades\Cache::forget('shop_categories');
        \Illuminate\Support\Facades\Cache::forget('shop_category_counts');
        \Illuminate\Support\Facades\Cache::forget('shop_price_range');
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');

        return redirect()->route('admin.products')->with('success', 'Product created successfully!');
    }

    public function productEdit(Product $product): View
    {
        $categories = \App\Models\Category::with('children')
            ->whereNull('parent_id')
            ->active()
            ->orderBy('order')
            ->get();
        
        return view('admin.product-edit', compact('product', 'categories'));
    }

    public function productUpdate(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'quantity' => 'required|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0|lt:price',
            'offer_starts_at' => 'nullable|date',
            'offer_ends_at' => 'nullable|date|after:offer_starts_at',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|in:figures,knives,stickers',
            'is_preorder' => 'boolean',
            'is_upcoming' => 'boolean',
            'price_tba' => 'boolean',
            'is_bookable' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:10240',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'integer',
            'rating' => 'nullable|integer|min:0|max:5',
            'reviews' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_limited_edition' => 'boolean',
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
        $validated['is_new'] = $request->has('is_new');
        $validated['is_bestseller'] = $request->has('is_bestseller');
        $validated['is_limited_edition'] = $request->has('is_limited_edition');
        $validated['is_preorder'] = $request->has('is_preorder');
        $validated['is_upcoming'] = $request->has('is_upcoming');
        $validated['price_tba'] = $request->has('price_tba');
        $validated['is_bookable'] = $request->has('is_bookable');

        $product->update($validated);

        if (!empty($newImagePaths)) {
            foreach ($newImagePaths as $pathData) {
                $product->images()->create($pathData);
            }
        }

        // Ensure primary image set to first available
        $primary = $product->images()->orderBy('sort_order')->first();
        $product->update(['image' => $primary->path ?? null]);

        // Clear relevant caches
        \Illuminate\Support\Facades\Cache::forget('homepage_data');
        \Illuminate\Support\Facades\Cache::forget('shop_categories');
        \Illuminate\Support\Facades\Cache::forget('shop_category_counts');
        \Illuminate\Support\Facades\Cache::forget('shop_price_range');
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
        \Illuminate\Support\Facades\Cache::forget('product_' . $product->id);
        \Illuminate\Support\Facades\Cache::forget('deals_page_data');
        // Clear paginated shop products cache
        $this->clearShopProductsCache();

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

        // Clear relevant caches
        \Illuminate\Support\Facades\Cache::forget('homepage_data');
        \Illuminate\Support\Facades\Cache::forget('shop_categories');
        \Illuminate\Support\Facades\Cache::forget('shop_category_counts');
        \Illuminate\Support\Facades\Cache::forget('shop_price_range');
        \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
        \Illuminate\Support\Facades\Cache::forget('product_' . $product->id);
        \Illuminate\Support\Facades\Cache::forget('deals_page_data');
        // Clear paginated shop products cache
        $this->clearShopProductsCache();

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully!');
    }

    /**
     * Clear shop products pagination cache
     */
    private function clearShopProductsCache(): void
    {
        // Clear first 10 pages of shop products cache
        for ($page = 1; $page <= 10; $page++) {
            \Illuminate\Support\Facades\Cache::forget('shop_products_page_12_' . $page);
        }
    }

    public function categories(): View
    {
        $categories = \App\Models\Category::with('children', 'parent')
            ->orderBy('order')
            ->get();
        
        return view('admin.categories', compact('categories'));
    }

    public function categoryCreate(): View
    {
        $parentCategories = \App\Models\Category::whereNull('parent_id')
            ->orderBy('order')
            ->get();
        
        return view('admin.category-create', compact('parentCategories'));
    }

    public function categoryStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories',
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['order'] = $validated['order'] ?? 0;

        \App\Models\Category::create($validated);

        // Clear relevant caches
        \Illuminate\Support\Facades\Cache::forget('homepage_data');
        \Illuminate\Support\Facades\Cache::forget('shop_categories');
        \Illuminate\Support\Facades\Cache::forget('shop_category_counts');

        return redirect()->route('admin.categories')->with('success', 'Category created successfully!');
    }

    public function categoryEdit(\App\Models\Category $category): View
    {
        $parentCategories = \App\Models\Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('order')
            ->get();
        
        return view('admin.category-edit', compact('category', 'parentCategories'));
    }

    public function categoryUpdate(Request $request, \App\Models\Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);

        // Clear relevant caches
        \Illuminate\Support\Facades\Cache::forget('homepage_data');
        \Illuminate\Support\Facades\Cache::forget('shop_categories');
        \Illuminate\Support\Facades\Cache::forget('shop_category_counts');

        return redirect()->route('admin.categories')->with('success', 'Category updated successfully!');
    }

    public function categoryDestroy(\App\Models\Category $category): RedirectResponse
    {
        $category->delete();

        // Clear relevant caches
        \Illuminate\Support\Facades\Cache::forget('homepage_data');
        \Illuminate\Support\Facades\Cache::forget('shop_categories');
        \Illuminate\Support\Facades\Cache::forget('shop_category_counts');

        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully!');
    }

    public function visitors(): View
    {
        // Get visitor stats from persistent visitors table
        $totalVisitors = Visitor::count();
        
        $todayVisitors = Visitor::where('last_visit_at', '>=', Carbon::today())
            ->count();
        
        $weekVisitors = Visitor::where('last_visit_at', '>=', Carbon::now()->subWeek())
            ->count();
        
        $monthVisitors = Visitor::where('last_visit_at', '>=', Carbon::now()->subMonth())
            ->count();
        
        // Get total visits (sum of all visit counts)
        $totalVisits = Visitor::sum('visit_count');
        
        // Get recent visitors
        $recentSessions = Visitor::select('ip_address', 'user_agent', 'device_type', 'browser', 'os', 'last_visit_at', 'visit_count')
            ->orderBy('last_visit_at', 'desc')
            ->take(50)
            ->get()
            ->map(function ($visitor) {
                return [
                    'ip_address' => $visitor->ip_address ?? 'Unknown',
                    'user_agent' => $visitor->user_agent ?? 'Unknown',
                    'device_type' => $visitor->device_type ?? 'Unknown',
                    'browser' => $visitor->browser ?? 'Unknown',
                    'os' => $visitor->os ?? 'Unknown',
                    'date_time' => $visitor->last_visit_at->format('Y-m-d H:i:s'),
                    'visit_count' => $visitor->visit_count,
                ];
            });
        
        return view('admin.visitors', [
            'totalVisitors' => $totalVisitors,
            'todayVisitors' => $todayVisitors,
            'weekVisitors' => $weekVisitors,
            'monthVisitors' => $monthVisitors,
            'totalVisits' => $totalVisits,
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
        // Cache financial data for 5 minutes (admin data changes frequently)
        $cacheKey = 'admin_financial_data';
        
        $financialData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () {
            // Get confirmed orders (excluding pending, cancelled, deleted orders, and pre-order bookings)
            // Pre-orders are excluded from financial calculations until converted to active orders
            $confirmedOrders = Order::notDeleted()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where('is_preorder_booking', false)
                ->get();

            // Total Revenue (subtotal - discount, excluding delivery charges)
            $totalRevenue = $confirmedOrders->sum(function($order) {
                return $order->subtotal - $order->discount;
            });

            // This Month Revenue (excluding pre-orders)
            $thisMonthOrders = Order::notDeleted()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where('is_preorder_booking', false)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->get();
            
            $thisMonthRevenue = $thisMonthOrders->sum(function($order) {
                return $order->subtotal - $order->discount;
            });

            // Calculate Total Expenses (product costs + operational expenses)
            // Product costs from sold items (excluding pre-orders)
            $productCosts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->where('orders.is_deleted', false)
                ->where('orders.is_preorder_booking', false)
                ->select(DB::raw('SUM(order_items.quantity * products.cost_price) as total_cost'))
                ->first();
            
            $totalProductCosts = $productCosts->total_cost ?? 0;
            
            // Operational expenses (ads, shipping, courier, packaging, etc.)
            $totalOperationalExpenses = \App\Models\Expense::sum('amount');
            
            // Total Expenses = Product Costs + Operational Expenses
            $totalExpenses = $totalProductCosts + $totalOperationalExpenses;
            
            // Pure Profit = Revenue - Total Expenses
            $pureProfit = $totalRevenue - $totalExpenses;

            // This Month Expenses
            $thisMonthExpenses = \App\Models\Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('amount');
            
            $thisMonthProductCosts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->whereIn('orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
                ->where('orders.is_deleted', false)
                ->where('orders.is_preorder_booking', false)
                ->whereMonth('orders.created_at', now()->month)
                ->whereYear('orders.created_at', now()->year)
                ->select(DB::raw('SUM(order_items.quantity * products.cost_price) as total_cost'))
                ->first();
            
            $thisMonthTotalExpenses = ($thisMonthProductCosts->total_cost ?? 0) + $thisMonthExpenses;
            $thisMonthProfit = $thisMonthRevenue - $thisMonthTotalExpenses;

            // Total Orders
            $totalOrders = $confirmedOrders->count();

            // Average Order Value (excluding delivery)
            $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            // Max Order Value (for progress bar calculation, excluding pre-orders)
            $maxOrderValue = Order::notDeleted()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where('is_preorder_booking', false)
                ->selectRaw('MAX(subtotal - discount) as max_order')
                ->first()
                ->max_order ?? 1;

            // Monthly Order Goal (based on highest monthly orders or total orders, excluding pre-orders)
            $monthlyOrderGoal = Order::notDeleted()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where('is_preorder_booking', false)
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as order_count')
                ->groupBy('year', 'month')
                ->orderBy('order_count', 'DESC')
                ->first()
                ->order_count ?? $totalOrders;

            // Recent Transactions (last 20 orders, excluding pre-orders)
            $recentTransactions = Order::notDeleted()
                ->where('status', '!=', 'pending')
                ->where('status', '!=', 'cancelled')
                ->where('is_preorder_booking', false)
                ->with(['user'])
                ->latest()
                ->take(20)
                ->get()
                ->map(function ($order) {
                    return [
                        'transaction_id' => $order->transaction_number ?? 'N/A',
                        'order_id' => $order->id,
                        'amount' => $order->subtotal - $order->discount, // Revenue without delivery
                        'payment_method' => strtoupper($order->payment_method ?? 'N/A'),
                        'status' => ucfirst($order->status),
                        'date' => $order->created_at->format('Y-m-d H:i:s'),
                    ];
                });
            
            return [
                'totalRevenue' => $totalRevenue,
                'thisMonthRevenue' => $thisMonthRevenue,
                'totalExpenses' => $totalExpenses,
                'totalProductCosts' => $totalProductCosts,
                'totalOperationalExpenses' => $totalOperationalExpenses,
                'pureProfit' => $pureProfit,
                'thisMonthExpenses' => $thisMonthTotalExpenses,
                'thisMonthProfit' => $thisMonthProfit,
                'totalOrders' => $totalOrders,
                'averageOrder' => $averageOrder,
                'maxOrderValue' => $maxOrderValue,
                'monthlyOrderGoal' => $monthlyOrderGoal,
                'recentTransactions' => $recentTransactions,
            ];
        });
        
        return view('admin.financial', $financialData);
    }

    public function coupons(): View
    {
        $coupons = Coupon::latest()->get();

        return view('admin.coupons', [
            'coupons' => $coupons,
        ]);
    }

    public function couponCreate(): View
    {
        return view('admin.coupon-create');
    }

    public function couponStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        // Convert code to uppercase
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        Coupon::create($validated);

        return redirect()->route('admin.coupons')->with('success', 'Coupon created successfully!');
    }

    public function couponEdit(Coupon $coupon): View
    {
        return view('admin.coupon-edit', compact('coupon'));
    }

    public function couponUpdate(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_order' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        $coupon->update($validated);

        return redirect()->route('admin.coupons')->with('success', 'Coupon updated successfully!');
    }

    public function couponDestroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return redirect()->route('admin.coupons')->with('success', 'Coupon deleted successfully!');
    }

    public function popupSettings(): View
    {
        $settings = PopupSetting::getSettings();

        return view('admin.popup-settings', compact('settings'));
    }

    public function updatePopupSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'main_heading' => 'nullable|string|max:500',
            'subheading' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:1000',
            'discount_text' => 'nullable|string|max:100',
            'discount_amount' => 'nullable|string|max:100',
            'badge_text' => 'nullable|string|max:100',
            'button_text' => 'required|string|max:50',
            'button_url' => 'required|string|max:255',
            'is_active' => 'boolean',
            'display_delay' => 'required|integer|min:0|max:30000',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $settings = PopupSetting::first();
        
        if ($settings) {
            $settings->update($validated);
        } else {
            PopupSetting::create($validated);
        }

        return redirect()->route('admin.popup-settings')->with('success', 'Popup settings updated successfully!');
    }

    /**
     * Show notification sending page
     */
    public function sendNotifications(): View
    {
        $products = Product::where('is_active', true)->latest()->get();
        
        // Count unique emails from both users and orders
        $userEmails = User::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', '!=', 'ifti3061@gmail.com')
            ->pluck('email')
            ->unique()
            ->count();
        
        $orderEmails = Order::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', '!=', 'ifti3061@gmail.com')
            ->pluck('email')
            ->unique()
            ->count();
        
        // Merge and get unique count
        $allUserEmails = User::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', '!=', 'ifti3061@gmail.com')
            ->pluck('email')
            ->unique()
            ->toArray();
        
        $allOrderEmails = Order::whereNotNull('email')
            ->where('email', '!=', '')
            ->where('email', '!=', 'ifti3061@gmail.com')
            ->pluck('email')
            ->unique()
            ->toArray();
        
        $totalUsers = count(array_unique(array_merge($allUserEmails, $allOrderEmails)));
        
        // Get stock notifications grouped by product
        $stockNotifications = StockNotification::with('product')
            ->where('notified', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('product_id');

        // SMS: balance and recipients (users + orders with name)
        $smsBalance = null;
        $smsRecipientsFromUsers = [];
        $smsRecipientsFromOrders = [];
        $smsConfigured = !empty(config('services.sms_net_bd.api_key'));
        if ($smsConfigured) {
            $smsService = new SmsNetBdService();
            $balanceResult = $smsService->getBalance();
            $smsBalance = $balanceResult['success'] ? $balanceResult['balance'] : null;

            $usersWithPhone = User::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get(['id', 'name', 'phone']);
            foreach ($usersWithPhone as $u) {
                $phone = SmsNetBdService::normalizePhone($u->phone);
                if (strlen($phone) >= 11) {
                    $smsRecipientsFromUsers[] = [
                        'phone' => $phone,
                        'name' => $u->name ?: ('User #' . $u->id),
                        'id' => $u->id,
                    ];
                }
            }

            $ordersWithPhone = Order::notDeleted()
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get(['id', 'name', 'phone']);
            foreach ($ordersWithPhone as $o) {
                $phone = SmsNetBdService::normalizePhone($o->phone);
                if (strlen($phone) >= 11) {
                    $smsRecipientsFromOrders[] = [
                        'phone' => $phone,
                        'name' => $o->name ?: ('Order #' . $o->id),
                        'order_id' => $o->id,
                    ];
                }
            }
        }
        
        return view('admin.send-notifications', compact(
            'products', 'totalUsers', 'stockNotifications',
            'smsBalance', 'smsRecipientsFromUsers', 'smsRecipientsFromOrders', 'smsConfigured'
        ));
    }

    /**
     * Send SMS via SMS.net.bd (admin send notifications page)
     */
    public function sendSms(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'msg' => 'required|string|max:1000',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'string|regex:/^880\d{10,11}$/',
        ], [
            'recipients.required' => 'Select at least one recipient.',
            'recipients.*.regex' => 'Invalid phone number format (use 880XXXXXXXXXX).',
        ]);

        $to = implode(',', array_unique($validated['recipients']));
        $smsService = new SmsNetBdService();
        $result = $smsService->sendSms($validated['msg'], $to);

        if ($result['success']) {
            return redirect()->route('admin.send-notifications')
                ->with('success', 'SMS sent successfully. Request ID: ' . ($result['request_id'] ?? 'N/A'));
        }

        return redirect()->back()
            ->withInput()
            ->with('error', 'SMS failed: ' . ($result['error'] ?? 'Unknown error'));
    }

    /**
     * Send special offer notification to all users (in-app + email)
     */
    public function sendSpecialOffer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'url' => 'nullable|url',
        ]);

        $title = $validated['title'];
        $message = $validated['message'];
        $url = $validated['url'] ?? null;

        NotificationService::specialOffer($title, $message, $url);

        try {
            $mailDriver = config('mail.default');
            if ($mailDriver === 'log') {
                return redirect()->back()->with('success', 'Special offer in-app notifications sent. Email is in "log" mode; configure SMTP in .env to send emails.');
            }

            $userEmails = User::whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
                ->unique()
                ->values()
                ->toArray();

            $orderEmails = Order::whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
                ->unique()
                ->values()
                ->toArray();

            $allEmails = array_unique(array_merge($userEmails, $orderEmails));

            if (empty($allEmails)) {
                return redirect()->back()->with('success', 'Special offer in-app notifications sent. No valid email addresses found for email delivery.');
            }

            $sentCount = 0;
            $failedCount = 0;
            $firstError = null;

            foreach ($allEmails as $email) {
                try {
                    $mailable = new \App\Mail\SpecialOfferNotification($title, $message, $url);
                    $result = EmailService::sendWithFallback($mailable, $email, 'special offer');
                    if ($result['success']) {
                        $sentCount++;
                    } else {
                        $failedCount++;
                        if (!$firstError) {
                            $firstError = $result['error'] ?? $result['message'] ?? 'Unknown error';
                        }
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    if (!$firstError) {
                        $firstError = $e->getMessage();
                    }
                    \Log::error('Special offer email failed', ['email' => $email, 'error' => $e->getMessage()]);
                }
            }

            $msg = "Special offer sent. In-app notifications created. Emails: {$sentCount} sent";
            if ($failedCount > 0) {
                $msg .= ", {$failedCount} failed";
                if ($firstError) {
                    $msg .= " (" . substr($firstError, 0, 80) . ")";
                }
            }
            $msg .= ".";

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            \Log::error('Special offer email batch failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('success', 'Special offer in-app notifications sent. Email delivery failed: ' . $e->getMessage());
        }
    }

    /**
     * Send new product notification to all users
     */
    public function sendNewProductNotification(Product $product): RedirectResponse
    {
        try {
            // Check email configuration first
            $mailDriver = config('mail.default');
            if ($mailDriver === 'log') {
                return redirect()->back()->with('error', 'Email is configured to "log" mode. Please configure SMTP settings in your .env file (MAIL_MAILER=smtp, MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, etc.) to actually send emails.');
            }
            
            // Collect unique emails from both users table and orders table
            // Filter out empty, null, and invalid emails
            $userEmails = User::whereNotNull('email')
                ->where('email', '!=', '')
                ->where('email', '!=', 'ifti3061@gmail.com')
                ->pluck('email')
                ->filter(function($email) {
                    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
                })
                ->unique()
                ->toArray();
            
            $orderEmails = Order::whereNotNull('email')
                ->where('email', '!=', '')
                ->where('email', '!=', 'ifti3061@gmail.com')
                ->pluck('email')
                ->filter(function($email) {
                    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
                })
                ->unique()
                ->toArray();
            
            // Merge and get unique emails
            $allEmails = array_unique(array_merge($userEmails, $orderEmails));
            
            // Filter out invalid email addresses
            $validEmails = array_filter($allEmails, function($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            });
            
            if (empty($validEmails)) {
                return redirect()->back()->with('error', 'No valid email addresses found to send notifications to.');
            }
            
            $sentCount = 0;
            $failedCount = 0;
            $failedEmails = [];
            $firstError = null;
            
            // Send email to each unique email address
            foreach ($validEmails as $email) {
                try {
                    $result = EmailService::sendWithFallback(
                        new \App\Mail\NewProductNotification($product),
                        $email,
                        'new product notification'
                    );
                    
                    if ($result['success']) {
                        $sentCount++;
                    } else {
                        $failedCount++;
                        if (!$firstError) {
                            $firstError = $result['error'] ?? $result['message'] ?? 'Unknown error';
                        }
                        $failedEmails[] = $email;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send new product notification email', [
                        'email' => $email,
                        'product_id' => $product->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failedCount++;
                    if (!$firstError) {
                        $firstError = $e->getMessage();
                    }
                    $failedEmails[] = $email;
                }
            }
            
            // Log failed emails for debugging
            if ($failedCount > 0) {
                \Log::warning('New product notification failures', [
                    'product_id' => $product->id,
                    'failed_count' => $failedCount,
                    'total_emails' => count($validEmails),
                    'first_error' => $firstError,
                    'sample_failed_emails' => array_slice($failedEmails, 0, 5), // Log first 5 failures
                ]);
            }
            
            // Also create in-app notifications for registered users
            NotificationService::newProduct($product);
            
            $message = "New product notification sent to {$sentCount} email address" . ($sentCount !== 1 ? 'es' : '');
            if ($failedCount > 0) {
                $message .= ". {$failedCount} failed";
                if ($firstError) {
                    $message .= " (Error: " . substr($firstError, 0, 100) . ")";
                }
                $message .= ". Check logs for details.";
            }
            
            if ($sentCount > 0) {
                return redirect()->back()->with('success', $message);
            } else {
                $errorMsg = "Failed to send notifications to all {$failedCount} email addresses.";
                if ($firstError) {
                    $errorMsg .= " Error: " . substr($firstError, 0, 150);
                }
                $errorMsg .= " Please check your email configuration (MAIL_MAILER, MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD) in .env file.";
                return redirect()->back()->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send new product notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()->with('error', 'Failed to send notifications: ' . $e->getMessage());
        }
    }

    /**
     * Costing & Expenses Management (formerly analytics)
     */
    public function costing(): View
    {
        // Get all expenses
        $expenses = \App\Models\Expense::with(['order', 'product'])
            ->orderBy('expense_date', 'desc')
            ->take(50)
            ->get();

        // Calculate expense totals by category
        $expensesByCategory = \App\Models\Expense::select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->category => $item->total];
            });

        // Total expenses
        $totalExpenses = \App\Models\Expense::sum('amount');

        // Calculate product costs (sold items only)
        $productCosts = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->where('orders.is_deleted', false)
            ->select(DB::raw('SUM(order_items.quantity * products.cost_price) as total_cost'))
            ->first();

        $totalProductCosts = $productCosts->total_cost ?? 0;

        // Get products with cost info
        $products = Product::select('id', 'name', 'price', 'cost_price', 'quantity')
            ->where('is_active', true)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'selling_price' => $product->price,
                    'cost_price' => $product->cost_price,
                    'profit_per_unit' => $product->price - $product->cost_price,
                    'stock' => $product->quantity,
                ];
            });

        return view('admin.costing', compact(
            'expenses',
            'expensesByCategory',
            'totalExpenses',
            'totalProductCosts',
            'products'
        ));
    }

    /**
     * Store new expense
     */
    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:shipping,ads,courier,packaging,damaged,returned,lost,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'order_id' => 'nullable|exists:orders,id',
            'product_id' => 'nullable|exists:products,id',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        \App\Models\Expense::create($validated);

        return redirect()->back()->with('success', 'Expense added successfully!');
    }

    /**
     * Delete expense
     */
    public function deleteExpense(\App\Models\Expense $expense): RedirectResponse
    {
        $expense->delete();
        return redirect()->back()->with('success', 'Expense deleted successfully!');
    }

    /**
     * Update product cost price
     */
    public function updateProductCost(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'cost_price' => 'required|numeric|min:0',
        ]);

        $product->update(['cost_price' => $validated['cost_price']]);

        return redirect()->back()->with('success', 'Product cost updated successfully!');
    }

    /**
     * Export sales report to Excel
     */
    public function exportSalesReport(Request $request)
    {
        $period = $request->get('period', 'daily');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $salesReport = AnalyticsService::getSalesReport($period, $startDate, $endDate);
        
        $fileName = 'sales_report_' . $period . '_' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(new SalesReportExport($salesReport, $period), $fileName);
    }

    /**
     * Clear analytics cache
     */
    public function clearAnalyticsCache(): RedirectResponse
    {
        AnalyticsService::clearCache();
        
        return redirect()->back()->with('success', 'Analytics cache cleared successfully!');
    }

    /**
     * Soft delete an order (hide from admin, no customer notification)
     */
    public function deleteOrder(Order $order): RedirectResponse
    {
        $order->softDelete();
        
        return redirect()->back()->with('success', "Order #{$order->id} has been hidden from the admin panel.");
    }

    /**
     * Restore a soft-deleted order
     */
    public function restoreOrder(Order $order): RedirectResponse
    {
        $order->restore();
        
        return redirect()->back()->with('success', "Order #{$order->id} has been restored.");
    }

    public function convertPreorderToActive(Order $order): RedirectResponse
    {
        if (!$order->is_preorder_booking) {
            return redirect()->back()->with('error', 'This order is not a pre-order booking.');
        }

        $order->update([
            'is_preorder_booking' => false,
        ]);

        // Log the change
        \App\Models\OrderChange::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'change_type' => 'order_updated',
            'description' => 'Pre-order converted to active order - now included in financial calculations',
            'old_data' => ['is_preorder_booking' => true],
            'new_data' => ['is_preorder_booking' => false],
        ]);

        return redirect()->route('admin.orders', ['view' => 'preorder'])
            ->with('success', 'Order #' . $order->id . ' has been converted to active order and is now included in financial calculations.');
    }

    /**
     * Send stock available notification to all users who requested it
     */
    public function sendStockNotification(Product $product): RedirectResponse
    {
        try {
            // Check email configuration first
            $mailDriver = config('mail.default');
            if ($mailDriver === 'log') {
                return redirect()->back()->with('error', 'Email is configured to "log" mode. Please configure SMTP settings in your .env file to actually send emails.');
            }
            
            // Get all stock notifications for this product that haven't been notified
            // Filter out null, empty, and invalid emails
            $notifications = StockNotification::where('product_id', $product->id)
                ->where('notified', false)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();
            
            if ($notifications->isEmpty()) {
                return redirect()->back()->with('error', 'No pending stock notifications with valid email addresses found for this product.');
            }
            
            // Filter out invalid email addresses
            $validNotifications = $notifications->filter(function($notification) {
                return filter_var($notification->email, FILTER_VALIDATE_EMAIL) !== false;
            });
            
            if ($validNotifications->isEmpty()) {
                return redirect()->back()->with('error', 'No valid email addresses found in stock notification requests.');
            }
            
            $sentCount = 0;
            $failedCount = 0;
            $firstError = null;
            
            // Send email to each notification request
            foreach ($validNotifications as $notification) {
                try {
                    $result = EmailService::sendWithFallback(
                        new \App\Mail\StockAvailableNotification($product),
                        $notification->email,
                        'stock available notification'
                    );
                    
                    if ($result['success']) {
                        // Mark as notified
                        $notification->update([
                            'notified' => true,
                            'notified_at' => now(),
                        ]);
                        $sentCount++;
                    } else {
                        $failedCount++;
                        if (!$firstError) {
                            $firstError = $result['error'] ?? $result['message'] ?? 'Unknown error';
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send stock notification email', [
                        'email' => $notification->email,
                        'product_id' => $product->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failedCount++;
                    if (!$firstError) {
                        $firstError = $e->getMessage();
                    }
                }
            }
            
            $message = "Stock notification sent to {$sentCount} email address" . ($sentCount !== 1 ? 'es' : '');
            if ($failedCount > 0) {
                $message .= ". {$failedCount} failed";
                if ($firstError) {
                    $message .= " (Error: " . substr($firstError, 0, 100) . ")";
                }
                $message .= ". Check logs for details.";
            }
            
            if ($sentCount > 0) {
                return redirect()->back()->with('success', $message);
            } else {
                $errorMsg = "Failed to send notifications to all {$failedCount} email addresses.";
                if ($firstError) {
                    $errorMsg .= " Error: " . substr($firstError, 0, 150);
                }
                $errorMsg .= " Please check your email configuration in .env file.";
                return redirect()->back()->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send stock notifications', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->back()->with('error', 'Failed to send stock notifications: ' . $e->getMessage());
        }
    }

    /**
     * Show special offers management
     */
    public function specialOffers(): View
    {
        $offers = SpecialOffer::orderBy('display_order')->get();
        return view('admin.special-offers', compact('offers'));
    }

    /**
     * Show create special offer form
     */
    public function specialOfferCreate(): View
    {
        return view('admin.special-offer-create');
    }

    /**
     * Store new special offer
     */
    public function specialOfferStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'badge_text' => 'required|string|max:255',
            'main_title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'feature_1' => 'nullable|string|max:255',
            'feature_2' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('special-offers', 'public');
        }

        $features = array_filter([
            $request->feature_1,
            $request->feature_2,
        ]);

        SpecialOffer::create([
            'badge_text' => $validated['badge_text'],
            'main_title' => $validated['main_title'],
            'subtitle' => $validated['subtitle'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'features' => $features,
            'is_active' => $request->has('is_active'),
            'display_order' => SpecialOffer::max('display_order') + 1,
        ]);

        return redirect()->route('admin.special-offers')->with('success', 'Special offer created successfully!');
    }

    /**
     * Show edit special offer form
     */
    public function specialOfferEdit(SpecialOffer $specialOffer): View
    {
        return view('admin.special-offer-edit', compact('specialOffer'));
    }

    /**
     * Update special offer
     */
    public function specialOfferUpdate(Request $request, SpecialOffer $specialOffer): RedirectResponse
    {
        $validated = $request->validate([
            'badge_text' => 'required|string|max:255',
            'main_title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'feature_1' => 'nullable|string|max:255',
            'feature_2' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $imagePath = $specialOffer->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::delete('public/' . $imagePath);
            }
            $imagePath = $request->file('image')->store('special-offers', 'public');
        }

        $features = array_filter([
            $request->feature_1,
            $request->feature_2,
        ]);

        $specialOffer->update([
            'badge_text' => $validated['badge_text'],
            'main_title' => $validated['main_title'],
            'subtitle' => $validated['subtitle'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'features' => $features,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.special-offers')->with('success', 'Special offer updated successfully!');
    }

    /**
     * Delete special offer
     */
    public function specialOfferDestroy(SpecialOffer $specialOffer): RedirectResponse
    {
        if ($specialOffer->image_path) {
            Storage::delete('public/' . $specialOffer->image_path);
        }
        
        $specialOffer->delete();
        
        return redirect()->route('admin.special-offers')->with('success', 'Special offer deleted successfully!');
    }

    /**
     * Display reviews management
     */
    public function reviews(): View
    {
        $reviews = Review::orderBy('order', 'asc')->get();
        
        return view('admin.reviews', compact('reviews'));
    }

    /**
     * Show create review form
     */
    public function reviewCreate(): View
    {
        $nextOrder = Review::max('order') + 1;
        
        return view('admin.review-create', compact('nextOrder'));
    }

    /**
     * Store new review
     */
    public function reviewStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'review_text' => 'nullable|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'order' => 'required|integer|min:0',
        ]);

        // Store image
        $imagePath = $request->file('image')->store('reviews', 'public');

        Review::create([
            'customer_name' => $validated['customer_name'],
            'review_text' => $validated['review_text'],
            'rating' => $validated['rating'],
            'image_path' => $imagePath,
            'order' => $validated['order'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.reviews')->with('success', 'Review added successfully!');
    }

    /**
     * Show edit review form
     */
    public function reviewEdit(Review $review): View
    {
        return view('admin.review-edit', compact('review'));
    }

    /**
     * Update review
     */
    public function reviewUpdate(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'review_text' => 'nullable|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'order' => 'required|integer|min:0',
        ]);

        $imagePath = $review->image_path;

        // Update image if new one uploaded
        if ($request->hasFile('image')) {
            // Delete old image
            if ($review->image_path) {
                Storage::delete('public/' . $review->image_path);
            }
            $imagePath = $request->file('image')->store('reviews', 'public');
        }

        $review->update([
            'customer_name' => $validated['customer_name'],
            'review_text' => $validated['review_text'],
            'rating' => $validated['rating'],
            'image_path' => $imagePath,
            'order' => $validated['order'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.reviews')->with('success', 'Review updated successfully!');
    }

    /**
     * Delete review
     */
    public function reviewDestroy(Review $review): RedirectResponse
    {
        // Delete image
        if ($review->image_path) {
            Storage::delete('public/' . $review->image_path);
        }
        
        $review->delete();
        
        return redirect()->route('admin.reviews')->with('success', 'Review deleted successfully!');
    }

    /**
     * Toggle review active status
     */
    public function reviewToggle(Review $review): RedirectResponse
    {
        $review->update([
            'is_active' => !$review->is_active,
        ]);

        $status = $review->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.reviews')->with('success', "Review {$status} successfully!");
    }
}

