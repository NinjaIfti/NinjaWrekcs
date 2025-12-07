<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
        
        // Calculate discounts (100 taka + 10%)
        $discountAmount = 100;
        $percentageDiscount = $cartSubTotal * 0.10;
        $totalDiscount = $discountAmount + $percentageDiscount;
        $finalTotal = max(0, $cartSubTotal - $totalDiscount);

        return view('checkout.index', compact('cartItems', 'cartTotal', 'cartSubTotal', 'totalDiscount', 'finalTotal'));
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

        // Add password field only for non-logged-in users
        if (!$isLoggedIn) {
            $rules['password'] = 'required|string|min:8';
            $rules['email'] = 'nullable|email|unique:users,email';
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
            $discountAmount = 100;
            $percentageDiscount = $cartSubTotal * 0.10;
            $totalDiscount = $discountAmount + $percentageDiscount;
            $finalTotal = max(0, $cartSubTotal - $totalDiscount);

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
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

            // Create order items
            foreach ($cartItems as $item) {
                $product = Product::find($item->id);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->id,
                    'product_name' => $item->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->price * $item->quantity,
                ]);

                // Update product quantity
                if ($product) {
                    $product->decrement('quantity', $item->quantity);
                }
            }

            DB::commit();

            // Clear cart
            \Cart::clear();

            return redirect()->route('checkout.success', $order)->with('success', 'Order placed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to place order. Please try again.')->withInput();
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
