<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Coupon;
use App\Models\IncompleteOrder;
use App\Mail\OrderConfirmation;
use App\Mail\AdminOrderNotification;
use App\Services\CartService;
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
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): View|RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        return view('checkout.index', [
            'lines' => $lines,
            'summary' => $this->cart->summary(),
        ]);
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

        $lines = $this->cart->lines();

        $cartSnapshot = $lines->map(fn ($line) => [
            'name' => $line->name,
            'quantity' => $line->quantity,
            'price' => $line->unitPrice,
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
                'subtotal' => $this->cart->summary()->subtotal,
                'ip_address' => $request->ip(),
                'last_activity_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function store(Request $request): RedirectResponse
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $summary = $this->cart->summary();
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
        if ($summary->hasBookingItems && $validated['payment_method'] === 'cod') {
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

            // Calculate delivery charge
            $deliveryCharge = $validated['delivery_location'] === 'inside_dhaka' ? 80 : 120;

            $coupon = null;
            $couponDiscount = 0;

            if (! empty($validated['coupon_code'])) {
                $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();

                if ($coupon && $coupon->isValid()) {
                    $couponDiscount = $coupon->calculateDiscount($summary->subtotal);
                }
            }

            $finalTotal = max(0, $summary->subtotal + $deliveryCharge - $couponDiscount);

            $orderEmail = $user?->email ?: ($validated['email'] ?? null);

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
                'subtotal' => $summary->subtotal,
                'discount' => $couponDiscount,
                'total' => $finalTotal,
                'payment_method' => $validated['payment_method'],
                'transaction_number' => $validated['transaction_number'] ?? null,
                'sending_number' => $validated['sending_number'] ?? null,
                'status' => 'pending',
                'save_info' => $request->has('save_info'),
                'terms_accepted' => true,
                'notes' => $request->input('notes'),
                'is_preorder_booking' => $summary->hasBookingItems,
                'booking_amount' => $summary->hasBookingItems ? $summary->bookingTotal : null,
            ]);

            // Increment coupon usage
            if ($coupon) {
                $coupon->incrementUsage();
            }

            foreach ($lines as $line) {
                // Lock the row that actually holds the stock before checking it, so
                // two simultaneous buyers of the last unit cannot both succeed.
                // Stock and variant validity are re-validated here rather than
                // trusted from add-to-cart time, since a variant can sell out or
                // be deactivated while sitting in the cart.
                if ($line->variant !== null) {
                    $locked = ProductVariant::whereKey($line->variant->id)->lockForUpdate()->first();
                    $available = $locked?->is_active ? (int) $locked->quantity : 0;
                } else {
                    $locked = Product::whereKey($line->product->id)->lockForUpdate()->first();
                    $available = $locked ? (int) $locked->quantity : 0;
                }

                if ($locked === null) {
                    throw new \Exception("{$line->name} is no longer available.");
                }

                if ($available < $line->quantity) {
                    throw new \Exception("Insufficient stock for {$line->name}. Only {$available} available.");
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line->product->id,
                    'product_variant_id' => $line->variant?->id,
                    'product_name' => $line->name,
                    'price' => $line->unitPrice,
                    'quantity' => $line->quantity,
                    'subtotal' => $line->lineTotal(),
                ]);

                // Stock is NOT taken here. It comes off when the order is
                // confirmed - see OrderStockService. The availability check
                // above still runs, so an order can never be placed for more
                // than exists, but the units stay sellable until someone
                // accepts the order.
                if (\App\Services\OrderStockService::holdsStock($order->status)) {
                    $locked->decrement('quantity', $line->quantity);
                }
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
            $this->cart->clear();

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
                'cart_items' => $lines->map(fn ($line) => [
                    'id' => $line->id,
                    'name' => $line->name,
                    'quantity' => $line->quantity,
                ])->all(),
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
