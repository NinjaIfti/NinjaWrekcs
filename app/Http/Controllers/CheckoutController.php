<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\IncompleteOrder;
use App\Mail\OrderConfirmation;
use App\Mail\AdminOrderNotification;
use App\Services\EmailService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CheckoutController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $cartItems = \Cart::getContent();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Calculate subtotal using original prices for all items
        $cartSubTotal = 0;
        $hasBookableItems = false;
        $totalBookingAmount = 0;
        
        foreach ($cartItems as $item) {
            // Check cart item attributes first (more reliable)
            $isBookable = false;
            if (isset($item->attributes->is_bookable)) {
                // Convert to boolean (handles string "1"/"0" or boolean true/false)
                $isBookable = (bool) $item->attributes->is_bookable;
            }
            
            if (!$isBookable) {
                $productId = is_string($item->id) && str_contains($item->id, '_') ? (int) explode('_', $item->id)[0] : $item->id;
                $product = Product::find($productId);
                $isBookable = $product && (bool) $product->is_bookable;
            }
            if ($isBookable) {
                $productId = is_string($item->id) && str_contains($item->id, '_') ? (int) explode('_', $item->id)[0] : $item->id;
                $product = Product::find($productId);
                if ($product) {
                    $originalPrice = (float) ($product->display_price ?? $product->price ?? 0);
                    $cartSubTotal += $originalPrice * $item->quantity;
                } else {
                    // Fallback: use original_price from attributes if product not found
                    $originalPrice = (float) ($item->attributes->original_price ?? $item->price);
                    $cartSubTotal += $originalPrice * $item->quantity;
                }
                $hasBookableItems = true;
                // Each bookable item has 200 booking fee
                $totalBookingAmount += 200 * $item->quantity;
            } else {
                $cartSubTotal += $item->price * $item->quantity;
            }
        }
        
        $cartTotal = \Cart::getTotal();
        
        // No automatic discounts - only coupon discounts apply
        $totalDiscount = 0;
        $finalTotal = $cartSubTotal;

        return view('checkout.index', compact('cartItems', 'cartTotal', 'cartSubTotal', 'totalDiscount', 'finalTotal', 'hasBookableItems', 'totalBookingAmount'));
    }

    public function validateCoupon(Request $request)
    {
        $couponCode = strtoupper($request->input('coupon_code'));
        $subtotal = $request->input('subtotal', 0);

        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code.'
            ]);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'This coupon is no longer valid or has expired.'
            ]);
        }

        if ($coupon->minimum_order && $subtotal < $coupon->minimum_order) {
            return response()->json([
                'success' => false,
                'message' => "Minimum order amount of ৳{$coupon->minimum_order} required for this coupon."
            ]);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'discount' => $discount,
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value
        ]);
    }

    /**
     * Autosave in-progress checkout details (name/phone/address typed so far)
     * so abandoned checkouts show up in the admin "Incomplete Orders" tab.
     */
    public function saveProgress(Request $request)
    {
        $name = trim((string) $request->input('name'));
        $phone = trim((string) $request->input('phone'));

        // Don't bother persisting a row until there's something worth following up on.
        if ($name === '' && $phone === '') {
            return response()->json(['success' => true]);
        }

        $cartItems = \Cart::getContent();
        $cartSnapshot = $cartItems->map(fn ($item) => [
            'name' => $item->name,
            'quantity' => $item->quantity,
            'price' => (float) $item->price,
        ])->values()->all();

        IncompleteOrder::updateOrCreate(
            ['session_id' => session()->getId()],
            [
                'user_id' => Auth::id(),
                'name' => $name !== '' ? $name : null,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $request->input('email') ?: null,
                'address' => $request->input('address') ?: null,
                'delivery_location' => $request->input('delivery_location') ?: null,
                'cart_snapshot' => $cartSnapshot,
                'subtotal' => $cartItems->sum(fn ($item) => $item->price * $item->quantity),
                'ip_address' => $request->ip(),
                'last_activity_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cartItems = \Cart::getContent();
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $isLoggedIn = Auth::check();

        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'delivery_location' => 'required|in:inside_dhaka,outside_dhaka',
            'payment_method' => 'required|in:bkash,cod',
            'transaction_number' => 'required_if:payment_method,bkash|nullable|string|max:50',
            'sending_number' => 'required_if:payment_method,bkash|nullable|string|max:20',
            'terms_accepted' => 'required|accepted',
            'coupon_code' => 'nullable|string|exists:coupons,code',
        ];

        // Check if cart has bookable items - if so, COD is not allowed
        // Use cart item attributes first, then fallback to database check
        $hasBookableItems = false;
        $totalBookingAmount = 0;
        foreach ($cartItems as $item) {
            // Check cart item attributes first (more reliable)
            $isBookable = false;
            if (isset($item->attributes->is_bookable)) {
                // Convert to boolean (handles string "1"/"0" or boolean true/false)
                $isBookable = (bool) $item->attributes->is_bookable;
            }
            
            // If not in attributes or not bookable, check database
            if (!$isBookable) {
                $product = Product::find($item->id);
                $isBookable = $product && (bool) $product->is_bookable;
            }
            
            // Only mark as bookable if explicitly true
            if ($isBookable === true) {
                $hasBookableItems = true;
                $totalBookingAmount += 200 * $item->quantity;
            }
        }

        if (!$isLoggedIn) {
            if ($request->boolean('create_account')) {
                $rules['email'] = 'required|email|max:255';
                $rules['password'] = 'required|string|min:8';
            } else {
                $rules['email'] = 'nullable|email|max:255';
            }
        }

        $validated = $request->validate($rules);

        // If bookable items exist, COD is not allowed
        if ($hasBookableItems && $validated['payment_method'] === 'cod') {
            return redirect()->back()->with('error', 'Cash on Delivery is not available for pre-order bookings. Please use Mobile Banking (bKash/Nagad).')->withInput();
        }

        DB::beginTransaction();
        try {
            $user = null;

            // Handle user account
            $accountCreated = false;
            $passwordUpdated = false;
            
            if (!$isLoggedIn && $request->boolean('create_account')) {
                $existingUser = \App\Models\User::where('email', $validated['email'])->first();

                if ($existingUser) {
                    $existingUser->update([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'address' => $validated['address'],
                        'password' => Hash::make($validated['password']),
                    ]);

                    $user = $existingUser;
                    $passwordUpdated = true;

                    Auth::login($user);
                } else {
                    $user = \App\Models\User::create([
                        'name' => $validated['name'],
                        'email' => $validated['email'],
                        'phone' => $validated['phone'],
                        'address' => $validated['address'],
                        'password' => Hash::make($validated['password']),
                        'email_verified_at' => now(),
                    ]);

                    $accountCreated = true;

                    Auth::login($user);

                    NotificationService::create(
                        $user,
                        NotificationService::TYPE_ORDER_UPDATE,
                        '🎉 Welcome to NinjaWrecks!',
                        "Your account has been created successfully! You can now track your orders and enjoy exclusive benefits.",
                        ['account_created' => true],
                        route('profile.index'),
                        '🎮',
                        'green'
                    );
                }
            } elseif (!$isLoggedIn) {
                $user = null;
            } else {
                $user = Auth::user();
                
                // Update user info if save_info is checked
                if ($request->has('save_info')) {
                    $user->update([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'address' => $validated['address'],
                    ]);
                }
            }

            // Calculate subtotal using original prices for all items
            $cartSubTotal = 0;
            $totalDiscount = 0;
            
            // Check for bookable items and calculate booking amount
            // Use cart item attributes first, then fallback to database check
            $hasBookableItems = false;
            $totalBookingAmount = 0;
            foreach ($cartItems as $item) {
                // Check cart item attributes first (more reliable)
                $isBookable = false;
                if (isset($item->attributes->is_bookable)) {
                    // Convert to boolean (handles string "1"/"0" or boolean true/false)
                    $isBookable = (bool) $item->attributes->is_bookable;
                }
                
                // If not in attributes or not bookable, check database
                if (!$isBookable) {
                    $product = Product::find($item->id);
                    $isBookable = $product && (bool) $product->is_bookable;
                }
                
                // Use original price for pre-order items, display_price (deal price) for regular items
                if ($isBookable) {
                    // Always fetch original price from database for pre-order items
                    $product = Product::find($item->id);
                    if ($product) {
                        $originalPrice = (float) ($product->price ?? 0);
                        if ($originalPrice == 0) {
                            $originalPrice = (float) ($product->display_price ?? 0);
                        }
                        $cartSubTotal += $originalPrice * $item->quantity;
                    } else {
                        // Fallback: use original_price from attributes if product not found
                        $originalPrice = (float) ($item->attributes->original_price ?? $item->price);
                        $cartSubTotal += $originalPrice * $item->quantity;
                    }
                    $hasBookableItems = true;
                    // Each bookable item has 200 booking fee
                    $totalBookingAmount += 200 * $item->quantity;
                } else {
                    // Regular items: use display_price (includes deals) from database
                    $product = Product::find($item->id);
                    if ($product) {
                        $displayPrice = (float) ($product->display_price ?? $product->price ?? 0);
                        $cartSubTotal += $displayPrice * $item->quantity;
                    } else {
                        // Fallback: use cart price if product not found
                        $cartSubTotal += $item->price * $item->quantity;
                    }
                }
            }
            
            // Calculate delivery charge
            $deliveryCharge = $validated['delivery_location'] === 'inside_dhaka' ? 80 : 120;
            
            // Apply coupon if provided
            $coupon = null;
            $couponDiscount = 0;
            if (!empty($validated['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();
                if ($coupon && $coupon->isValid()) {
                    $couponDiscount = $coupon->calculateDiscount($cartSubTotal);
                    $totalDiscount = $couponDiscount;
                }
            }
            
            $finalTotal = max(0, $cartSubTotal + $deliveryCharge - $totalDiscount);

            $orderEmail = $user?->email;
            if (!$orderEmail && !empty($validated['email'] ?? null)) {
                $orderEmail = $validated['email'];
            }

            // Create order
            $order = Order::create([
                'user_id' => $user?->id,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'coupon_discount' => $couponDiscount,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'delivery_location' => $validated['delivery_location'],
                'delivery_charge' => $deliveryCharge,
                'email' => $orderEmail,
                'subtotal' => $cartSubTotal,
                'discount' => $totalDiscount,
                'total' => $finalTotal,
                'payment_method' => $validated['payment_method'],
                'transaction_number' => $validated['transaction_number'] ?? null,
                'sending_number' => $validated['sending_number'] ?? null,
                'status' => 'pending',
                'save_info' => $request->has('save_info'),
                'terms_accepted' => true,
                'notes' => $request->input('notes'),
                'is_preorder_booking' => $hasBookableItems,
                'booking_amount' => $hasBookableItems ? $totalBookingAmount : null,
            ]);
            
            // Increment coupon usage
            if ($coupon) {
                $coupon->incrementUsage();
            }

            // Create order items
            foreach ($cartItems as $item) {
                $productId = is_string($item->id) && str_contains($item->id, '_')
                    ? (int) explode('_', $item->id)[0]
                    : $item->id;
                $product = Product::find($productId);

                if (!$product) {
                    throw new \Exception("Product with ID {$productId} not found.");
                }
                if ($product->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}. Only {$product->quantity} available, but {$item->quantity} requested.");
                }

                $isBookable = isset($item->attributes->is_bookable) && (bool) $item->attributes->is_bookable;
                $variantId = $item->attributes->variant_id ?? null;

                if ($isBookable) {
                    $itemPrice = (float) ($product->price ?? $product->display_price ?? 0);
                } elseif ($variantId) {
                    $itemPrice = (float) $item->price;
                } else {
                    $itemPrice = (float) ($product->display_price ?? $product->price ?? 0);
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_variant_id' => $variantId,
                    'product_name' => $item->name,
                    'price' => $itemPrice,
                    'quantity' => $item->quantity,
                    'subtotal' => $itemPrice * $item->quantity,
                ]);

                $product->decrement('quantity', $item->quantity);
            }

            DB::commit();

            // Clear admin dashboard cache when new order is placed
            \Illuminate\Support\Facades\Cache::forget('admin_dashboard_stats');
            \Illuminate\Support\Facades\Cache::forget('admin_financial_data');

            // Send order confirmation email when we have a valid address
            $order->load('items');
            if ($order->email && filter_var($order->email, FILTER_VALIDATE_EMAIL)) {
                $emailResult = EmailService::sendWithFallback(
                    new OrderConfirmation($order),
                    $order->email,
                    'order confirmation'
                );

                if (!$emailResult['success']) {
                    session()->flash('email_warning', 'Order placed successfully, but confirmation email could not be sent. Please check your email or contact support.');
                }
            }

            // Send admin notification email
            try {
                Mail::to('ifti3061@gmail.com')->send(new AdminOrderNotification($order));
            } catch (\Exception $e) {
                \Log::error('Failed to send admin order notification email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send notification
            NotificationService::orderPlaced($order);

            // Clear cart
            \Cart::clear();

            // This checkout completed, so it's no longer "incomplete"
            IncompleteOrder::where('session_id', session()->getId())->delete();

            // Success message
            if ($accountCreated) {
                $successMessage = 'Order placed successfully! 🎉 Your account has been created and you are now logged in. You can track your order from your profile.';
            } elseif ($passwordUpdated) {
                $successMessage = 'Order placed successfully! We found an existing account with your email, so we updated your password and logged you in.';
            } elseif (!$isLoggedIn && !$request->boolean('create_account')) {
                $successMessage = 'Order placed successfully! Your order number is #' . $order->id . '.';
                if (!empty($order->email)) {
                    $successMessage .= ' We sent a confirmation to your email.';
                }
            } else {
                $successMessage = 'Order placed successfully!';
            }

            // Store order ID in session so the success page can verify access
            // even if Auth::login() session regeneration doesn't carry over cleanly
            session(['checkout_order_id' => $order->id]);

            return redirect()->route('checkout.success', $order)
                ->with('success', $successMessage)
                ->with('account_created', $accountCreated)
                ->with('password_updated', $passwordUpdated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order placement failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'cart_items' => $cartItems->toArray(),
            ]);
            
            $errorMessage = 'Failed to place order: ';
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                $errorMessage .= 'Product not found or invalid.';
            } elseif (str_contains($e->getMessage(), 'duplicate entry')) {
                $errorMessage .= 'Email already exists. Please use a different email.';
            } elseif (str_contains($e->getMessage(), 'cart')) {
                $errorMessage .= 'Cart is empty or invalid.';
            } else {
                $errorMessage .= $e->getMessage();
            }
            
            return redirect()->back()->with('error', $errorMessage)->withInput();
        }
    }

    public function success(Order $order): View
    {
        // Allow access if:
        // 1. It is a guest order (no user attached) — open to the URL holder
        // 2. The authenticated user owns the order
        // 3. The order was placed in this very session (handles the case where
        //    Auth::login() session regeneration doesn't survive the redirect,
        //    which is the root cause of the intermittent 403)
        $placedInThisSession = session('checkout_order_id') == $order->id;

        if ($order->user_id !== null && Auth::id() !== $order->user_id && !$placedInThisSession) {
            abort(403);
        }

        return view('checkout.success', compact('order'));
    }
}
