<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $products = \App\Models\Product::where('is_active', true)
        ->latest()
        ->take(4)
        ->get();
    
    return view('welcome', compact('products'));
});

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
    Route::put('/orders/{order}/status', [\App\Http\Controllers\AdminController::class, 'updateOrderStatus'])->name('orders.update-status');
    
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
