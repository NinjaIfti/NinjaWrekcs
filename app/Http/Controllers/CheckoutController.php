<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Coupon;
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

        $cartTotal = \Cart::getTotal();
        $cartSubTotal = \Cart::getSubTotal();
        
        // No automatic discounts - only coupon discounts apply
        $totalDiscount = 0;
        $finalTotal = $cartSubTotal;

        return view('checkout.index', compact('cartItems', 'cartTotal', 'cartSubTotal', 'totalDiscount', 'finalTotal'));
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
            'transaction_number' => 'required|string|max:50',
            'sending_number' => 'required|string|max:20',
            'terms_accepted' => 'required|accepted',
        ];

        // Add password and email fields only for non-logged-in users
        if (!$isLoggedIn) {
            $rules['password'] = 'required|string|min:8';
            $rules['email'] = 'required|email|unique:users,email';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $user = null;

            // Create user account if not logged in
            if (!$isLoggedIn) {
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? $validated['phone'] . '@ninjawrekcs.com',
                    'phone' => $validated['phone'],
                    'address' => $validated['address'],
                    'password' => Hash::make($validated['password']),
                ]);

                // Auto-login the new user
                Auth::login($user);
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

            // Calculate totals
            $cartSubTotal = \Cart::getSubTotal();
            $totalDiscount = 0;
            
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
            
            $finalTotal = max(0, $cartSubTotal - $totalDiscount);

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'coupon_discount' => $couponDiscount,
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'email' => $user->email,
                'subtotal' => $cartSubTotal,
                'discount' => $totalDiscount,
                'total' => $finalTotal,
                'payment_method' => 'bkash',
                'transaction_number' => $validated['transaction_number'],
                'sending_number' => $validated['sending_number'],
                'status' => 'pending',
                'save_info' => $request->has('save_info'),
                'terms_accepted' => true,
                'notes' => $request->input('notes'),
            ]);
            
            // Increment coupon usage
            if ($coupon) {
                $coupon->incrementUsage();
            }

            // Create order items
            foreach ($cartItems as $item) {
                $product = Product::find($item->id);
                
                if (!$product) {
                    throw new \Exception("Product with ID {$item->id} not found.");
                }

                // Check stock availability
                if ($product->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for {$product->name}. Only {$product->quantity} available, but {$item->quantity} requested.");
                }
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->price * $item->quantity,
                ]);

                // Update product quantity
                $product->decrement('quantity', $item->quantity);
            }

            DB::commit();

            // Clear cart
            \Cart::clear();

            return redirect()->route('checkout.success', $order)->with('success', 'Order placed successfully!');
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
        if (Auth::id() !== $order->user_id) {
            abort(403);
        }

        return view('checkout.success', compact('order'));
    }
}
