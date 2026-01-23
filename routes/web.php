<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockNotificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    $products = \App\Models\Product::with('images')
        ->where('is_active', true)
        ->where('is_featured', true)
        ->latest()
        ->get();
    
    // If no featured products, fallback to latest active products
    if ($products->count() === 0) {
        $products = \App\Models\Product::with('images')
            ->where('is_active', true)
            ->latest()
            ->take(4)
            ->get();
    }
    
    // Get categories with products for showcase
    $categories = \App\Models\Category::with(['children', 'products' => function($query) {
        $query->where('is_active', true)->with('images')->latest()->take(4);
    }])
    ->whereNull('parent_id')
    ->whereIn('slug', ['valorant', 'csgo', 'pre-order-upcoming'])
    ->orderBy('order')
    ->get();
    
    return view('welcome', compact('products', 'categories'));
});

Route::get('/test-email', function () {
    $toEmail = request('to', 'ninjaifti3061@gmail.com');
    $type = request('type', 'simple'); // simple, order-confirmation, order-status
    
    try {
        if ($type === 'order-confirmation') {
            // Get the latest order
            $order = \App\Models\Order::with('items')->latest()->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders found in database. Please create an order first.'
                ], 404);
            }
            
            $result = \App\Services\EmailService::sendWithFallback(
                new \App\Mail\OrderConfirmation($order),
                $toEmail,
                'order confirmation test'
            );
            
            $result['order_id'] = $order->id;
            return response()->json($result);
            
        } elseif ($type === 'order-status') {
            // Get the latest order
            $order = \App\Models\Order::with('items')->latest()->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'No orders found in database. Please create an order first.'
                ], 404);
            }
            
            $result = \App\Services\EmailService::sendWithFallback(
                new \App\Mail\OrderStatusUpdated($order, 'pending'),
                $toEmail,
                'order status update test'
            );
            
            $result['order_id'] = $order->id;
            return response()->json($result);
            
        } else {
            // Simple test email
            Mail::raw('This is a test email from NinjaWrecks! Your Brevo SMTP configuration is working correctly. Sent at: ' . now()->format('Y-m-d H:i:s'), function ($message) use ($toEmail) {
                $message->to($toEmail)
                        ->subject('Test Email from NinjaWrecks - Brevo SMTP');
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Simple test email sent to ' . $toEmail
            ]);
        }
    } catch (\Exception $e) {
        \Log::error('Test email failed', [
            'type' => $type,
            'to' => $toEmail,
            'error' => $e->getMessage(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to send email: ' . $e->getMessage(),
            'error_type' => get_class($e),
            'error_file' => $e->getFile() . ':' . $e->getLine(),
        ], 500);
    }
})->name('test.email');

Route::get('/email-health', function () {
    $health = \App\Services\EmailService::healthCheck();
    
    return response()->json($health);
})->name('email.health');

Route::get('/email-test-connection', function () {
    $result = \App\Services\EmailService::testConnection();
    
    return response()->json($result);
})->name('email.test.connection');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/shipping', function () {
    return view('shipping');
})->name('shipping');

Route::get('/returns', function () {
    return view('returns');
})->name('returns');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/agent-code', function () {
    return view('agent-code');
})->name('agent-code');

// Sitemap
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Robots.txt
Route::get('/robots.txt', function () {
    $sitemapUrl = url('/sitemap.xml');
    $content = "User-agent: *\n";
    $content .= "Allow: /\n";
    $content .= "Disallow: /admin/\n";
    $content .= "Disallow: /profile/\n";
    $content .= "Disallow: /checkout/\n";
    $content .= "Disallow: /cart\n";
    $content .= "Disallow: /test-email\n\n";
    $content .= "# Sitemap\n";
    $content .= "Sitemap: {$sitemapUrl}\n";
    
    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

// Sitemap
Route::get('/sitemap.xml', function () {
    $products = \App\Models\Product::where('is_active', true)->get();
    
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Homepage
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . url('/') . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>daily</changefreq>' . "\n";
    $xml .= '    <priority>1.0</priority>' . "\n";
    $xml .= '  </url>' . "\n";
    
    // Shop
    $xml .= '  <url>' . "\n";
    $xml .= '    <loc>' . route('shop.index') . '</loc>' . "\n";
    $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
    $xml .= '    <changefreq>daily</changefreq>' . "\n";
    $xml .= '    <priority>0.9</priority>' . "\n";
    $xml .= '  </url>' . "\n";
    
    // Products
    foreach ($products as $product) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . route('shop.show', $product->slug ?? $product->id) . '</loc>' . "\n";
        $xml .= '    <lastmod>' . $product->updated_at->format('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>weekly</changefreq>' . "\n";
        $xml .= '    <priority>0.8</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }
    
    // Static pages
    $pages = [
        ['url' => route('about'), 'priority' => '0.7'],
        ['url' => route('contact'), 'priority' => '0.7'],
        ['url' => route('shipping'), 'priority' => '0.6'],
        ['url' => route('returns'), 'priority' => '0.6'],
        ['url' => route('faq'), 'priority' => '0.6'],
        ['url' => route('privacy'), 'priority' => '0.5'],
        ['url' => route('terms'), 'priority' => '0.5'],
    ];
    
    foreach ($pages as $page) {
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . $page['url'] . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>monthly</changefreq>' . "\n";
        $xml .= '    <priority>' . $page['priority'] . '</priority>' . "\n";
        $xml .= '  </url>' . "\n";
    }
    
    $xml .= '</urlset>';
    
    return response($xml, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');
Route::get('/shop/api/recent-purchases', [ShopController::class, 'recentPurchases'])->name('shop.recent-purchases');

// Deals Page
Route::get('/deals', function () {
    // Get products with active offers
    $offerProducts = \App\Models\Product::with('images', 'category')
        ->where('is_active', true)
        ->where(function($query) {
            $query->whereNotNull('offer_price')
                  ->where('offer_starts_at', '<=', now())
                  ->where('offer_ends_at', '>=', now());
        })
        ->latest()
        ->get();
    
    // Get products with sale prices
    $saleProducts = \App\Models\Product::with('images', 'category')
        ->where('is_active', true)
        ->whereNotNull('sale_price')
        ->whereNull('offer_price')
        ->latest()
        ->get();
    
    // Get featured deals (products marked as featured with discounts)
    $featuredDeals = \App\Models\Product::with('images', 'category')
        ->where('is_active', true)
        ->where('is_featured', true)
        ->where(function($query) {
            $query->whereNotNull('sale_price')
                  ->orWhere(function($q) {
                      $q->whereNotNull('offer_price')
                        ->where('offer_starts_at', '<=', now())
                        ->where('offer_ends_at', '>=', now());
                  });
        })
        ->latest()
        ->take(4)
        ->get();
    
    return view('deals.index', compact('offerProducts', 'saleProducts', 'featuredDeals'));
})->name('deals.index');

// Stock Notification Routes
Route::post('/stock-notification', [StockNotificationController::class, 'store'])->name('stock-notification.store');

// Cart Routes
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{itemId}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{itemId}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes (open to all - guest checkout enabled)
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/validate-coupon', [\App\Http\Controllers\CheckoutController::class, 'validateCoupon'])->name('checkout.validate-coupon');
Route::get('/checkout/success/{order}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/dashboard', function () {
    // Redirect admin users to admin dashboard
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    // Regular users go to profile instead of old dashboard
    return redirect()->route('profile.index');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\UserProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/orders/{order}', [\App\Http\Controllers\UserProfileController::class, 'showOrder'])->name('profile.orders.show');
    Route::post('/profile/personal-info', [\App\Http\Controllers\UserProfileController::class, 'updatePersonalInfo'])->name('profile.update.personal');
    
    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/profile/address', [\App\Http\Controllers\UserProfileController::class, 'updateAddress'])->name('profile.update.address');
    Route::post('/profile/password', [\App\Http\Controllers\UserProfileController::class, 'updatePassword'])->name('profile.update.password');
    
    // Old profile routes (keep for compatibility)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [\App\Http\Controllers\AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/create', [\App\Http\Controllers\AdminController::class, 'orderCreate'])->name('orders.create');
    Route::post('/orders', [\App\Http\Controllers\AdminController::class, 'orderStore'])->name('orders.store');
    Route::get('/orders/{order}/edit', [\App\Http\Controllers\AdminController::class, 'orderEdit'])->name('orders.edit');
    Route::put('/orders/{order}', [\App\Http\Controllers\AdminController::class, 'orderUpdate'])->name('orders.update');
    Route::get('/orders/export', [\App\Http\Controllers\AdminController::class, 'exportOrders'])->name('orders.export');
    Route::put('/orders/{order}/status', [\App\Http\Controllers\AdminController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::delete('/orders/{order}', [\App\Http\Controllers\AdminController::class, 'deleteOrder'])->name('orders.delete');
    Route::post('/orders/{order}/restore', [\App\Http\Controllers\AdminController::class, 'restoreOrder'])->name('orders.restore');
    Route::post('/orders/{order}/convert-to-active', [\App\Http\Controllers\AdminController::class, 'convertPreorderToActive'])->name('orders.convert-to-active');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    
    // Products Management
    Route::get('/products', [\App\Http\Controllers\AdminController::class, 'products'])->name('products');
    Route::get('/products/create', [\App\Http\Controllers\AdminController::class, 'productCreate'])->name('products.create');
    Route::post('/products', [\App\Http\Controllers\AdminController::class, 'productStore'])->name('products.store');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\AdminController::class, 'productEdit'])->name('products.edit');
    Route::put('/products/{product}', [\App\Http\Controllers\AdminController::class, 'productUpdate'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\AdminController::class, 'productDestroy'])->name('products.destroy');
    
    // Categories Management
    Route::get('/categories', [\App\Http\Controllers\AdminController::class, 'categories'])->name('categories');
    Route::get('/categories/create', [\App\Http\Controllers\AdminController::class, 'categoryCreate'])->name('categories.create');
    Route::post('/categories', [\App\Http\Controllers\AdminController::class, 'categoryStore'])->name('categories.store');
    Route::get('/categories/{category}/edit', [\App\Http\Controllers\AdminController::class, 'categoryEdit'])->name('categories.edit');
    Route::put('/categories/{category}', [\App\Http\Controllers\AdminController::class, 'categoryUpdate'])->name('categories.update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\AdminController::class, 'categoryDestroy'])->name('categories.destroy');
    
    // Featured Products Management
    Route::get('/featured-products', [\App\Http\Controllers\AdminController::class, 'featuredProducts'])->name('featured-products');
    Route::post('/featured-products', [\App\Http\Controllers\AdminController::class, 'updateFeaturedProducts'])->name('featured-products.update');
    
    // Reviews Management
    Route::get('/reviews', [\App\Http\Controllers\AdminController::class, 'reviews'])->name('reviews');
    Route::get('/reviews/create', [\App\Http\Controllers\AdminController::class, 'reviewCreate'])->name('reviews.create');
    Route::post('/reviews', [\App\Http\Controllers\AdminController::class, 'reviewStore'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [\App\Http\Controllers\AdminController::class, 'reviewEdit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [\App\Http\Controllers\AdminController::class, 'reviewUpdate'])->name('reviews.update');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\AdminController::class, 'reviewDestroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/toggle', [\App\Http\Controllers\AdminController::class, 'reviewToggle'])->name('reviews.toggle');
    
    // Coupons Management
    Route::get('/coupons', [\App\Http\Controllers\AdminController::class, 'coupons'])->name('coupons');
    Route::get('/coupons/create', [\App\Http\Controllers\AdminController::class, 'couponCreate'])->name('coupons.create');
    Route::post('/coupons', [\App\Http\Controllers\AdminController::class, 'couponStore'])->name('coupons.store');
    Route::get('/coupons/{coupon}/edit', [\App\Http\Controllers\AdminController::class, 'couponEdit'])->name('coupons.edit');
    Route::put('/coupons/{coupon}', [\App\Http\Controllers\AdminController::class, 'couponUpdate'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [\App\Http\Controllers\AdminController::class, 'couponDestroy'])->name('coupons.destroy');
    
    // Popup Settings
    Route::get('/popup-settings', [\App\Http\Controllers\AdminController::class, 'popupSettings'])->name('popup-settings');
    Route::put('/popup-settings', [\App\Http\Controllers\AdminController::class, 'updatePopupSettings'])->name('popup-settings.update');
    
    // Special Offers Management
    Route::get('/special-offers', [\App\Http\Controllers\AdminController::class, 'specialOffers'])->name('special-offers');
    Route::get('/special-offers/create', [\App\Http\Controllers\AdminController::class, 'specialOfferCreate'])->name('special-offers.create');
    Route::post('/special-offers', [\App\Http\Controllers\AdminController::class, 'specialOfferStore'])->name('special-offers.store');
    Route::get('/special-offers/{specialOffer}/edit', [\App\Http\Controllers\AdminController::class, 'specialOfferEdit'])->name('special-offers.edit');
    Route::put('/special-offers/{specialOffer}', [\App\Http\Controllers\AdminController::class, 'specialOfferUpdate'])->name('special-offers.update');
    Route::delete('/special-offers/{specialOffer}', [\App\Http\Controllers\AdminController::class, 'specialOfferDestroy'])->name('special-offers.destroy');
    
    // Notifications Management
    Route::get('/send-notifications', [\App\Http\Controllers\AdminController::class, 'sendNotifications'])->name('send-notifications');
    Route::post('/send-special-offer', [\App\Http\Controllers\AdminController::class, 'sendSpecialOffer'])->name('send-special-offer');
    Route::post('/send-new-product/{product}', [\App\Http\Controllers\AdminController::class, 'sendNewProductNotification'])->name('send-new-product');
    
    // Costing & Expenses Management (formerly Analytics)
    Route::get('/costing', [\App\Http\Controllers\AdminController::class, 'costing'])->name('costing');
    Route::post('/expenses', [\App\Http\Controllers\AdminController::class, 'storeExpense'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [\App\Http\Controllers\AdminController::class, 'deleteExpense'])->name('expenses.delete');
    Route::put('/products/{product}/cost', [\App\Http\Controllers\AdminController::class, 'updateProductCost'])->name('product.update-cost');
    
    Route::get('/visitors', [\App\Http\Controllers\AdminController::class, 'visitors'])->name('visitors');
    Route::get('/financial', [\App\Http\Controllers\AdminController::class, 'financial'])->name('financial');
});

require __DIR__.'/auth.php';
