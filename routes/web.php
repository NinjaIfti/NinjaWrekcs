<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    $products = \App\Models\Product::where('is_active', true)
        ->latest()
        ->take(4)
        ->get();
    
    return view('welcome', compact('products'));
});

Route::get('/test-email', function () {
    $toEmail = request('to', 'ninjaifti3061@gmail.com');
    
    try {
        Mail::raw('This is a test email from NinjaWrekcs! Your Brevo SMTP configuration is working correctly. Sent at: ' . now()->format('Y-m-d H:i:s'), function ($message) use ($toEmail) {
            $message->to($toEmail)
                    ->subject('Test Email from NinjaWrekcs - Brevo SMTP');
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Test email sent successfully to ' . $toEmail
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send email: ' . $e->getMessage()
        ], 500);
    }
})->name('test.email');

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

// Cart Routes
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{itemId}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{itemId}', [\App\Http\Controllers\CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');

// Checkout Routes
Route::get('/checkout', [\App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [\App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [\App\Http\Controllers\CheckoutController::class, 'success'])->name('checkout.success');

Route::get('/dashboard', function () {
    // Redirect admin users to admin dashboard
    if (Auth::check() && Auth::user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    // Regular users go to profile instead of old dashboard
    return redirect()->route('profile.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\UserProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/orders/{order}', [\App\Http\Controllers\UserProfileController::class, 'showOrder'])->name('profile.orders.show');
    Route::post('/profile/personal-info', [\App\Http\Controllers\UserProfileController::class, 'updatePersonalInfo'])->name('profile.update.personal');
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
    Route::get('/orders/export', [\App\Http\Controllers\AdminController::class, 'exportOrders'])->name('orders.export');
    Route::put('/orders/{order}/status', [\App\Http\Controllers\AdminController::class, 'updateOrderStatus'])->name('orders.update-status');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    
    // Products Management
    Route::get('/products', [\App\Http\Controllers\AdminController::class, 'products'])->name('products');
    Route::get('/products/create', [\App\Http\Controllers\AdminController::class, 'productCreate'])->name('products.create');
    Route::post('/products', [\App\Http\Controllers\AdminController::class, 'productStore'])->name('products.store');
    Route::get('/products/{product}/edit', [\App\Http\Controllers\AdminController::class, 'productEdit'])->name('products.edit');
    Route::put('/products/{product}', [\App\Http\Controllers\AdminController::class, 'productUpdate'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\AdminController::class, 'productDestroy'])->name('products.destroy');
    
    Route::get('/visitors', [\App\Http\Controllers\AdminController::class, 'visitors'])->name('visitors');
    Route::get('/financial', [\App\Http\Controllers\AdminController::class, 'financial'])->name('financial');
});

require __DIR__.'/auth.php';
