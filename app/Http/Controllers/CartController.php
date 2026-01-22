<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = \Cart::getContent();
        
        // Recalculate subtotal using original prices for pre-order items
        $cartSubTotal = 0;
        $hasBookableItems = false;
        
        foreach ($cartItems as $item) {
            // Check if item is bookable
            $isBookable = false;
            if (isset($item->attributes->is_bookable)) {
                $isBookable = (bool) $item->attributes->is_bookable;
            } else {
                $product = Product::find($item->id);
                $isBookable = $product && (bool) $product->is_bookable;
            }
            
            if ($isBookable) {
                $hasBookableItems = true;
                // For pre-order items, ALWAYS fetch original price from database
                $product = Product::find($item->id);
                if ($product) {
                    // Use the product's display_price or price (original, not reduced)
                    $originalPrice = (float) ($product->display_price ?? $product->price ?? 0);
                    $cartSubTotal += $originalPrice * $item->quantity;
                } else {
                    // Fallback: use original_price from attributes if product not found
                    $originalPrice = (float) ($item->attributes->original_price ?? $item->price);
                    $cartSubTotal += $originalPrice * $item->quantity;
                }
            } else {
                // Regular items: use cart price as-is
                $cartSubTotal += $item->price * $item->quantity;
            }
        }
        
        $cartTotal = \Cart::getTotal();
        
        return view('cart.index', compact('cartItems', 'cartTotal', 'cartSubTotal', 'hasBookableItems'));
    }

    public function add(Request $request, Product $product)
    {
        // Check if product is active and in stock
        if (!$product->is_active) {
            return redirect()->back()->with('error', 'This product is not available.');
        }

        // Prevent adding upcoming or price-tba items to cart
        if ($product->is_upcoming) {
            return redirect()->back()->with('error', 'This item is upcoming and cannot be added to cart yet.');
        }

        // Check if price is 0 or TBA - prevent adding to cart
        $displayPrice = $product->display_price ?? 0;
        if ($product->price_tba || $product->price == 0 || $displayPrice == 0 || !$displayPrice) {
            return redirect()->back()->with('error', 'Price will be announced later. Please check back once pricing is available.');
        }

        $quantity = $request->input('quantity', 1);
        
        // Check stock availability
        if ($product->quantity < $quantity) {
            return redirect()->back()->with('error', 'Insufficient stock. Only ' . $product->quantity . ' available.');
        }

        // Check if item already in cart
        $cartItem = \Cart::get($product->id);
        $currentQuantity = $cartItem ? $cartItem->quantity : 0;
        
        if (($currentQuantity + $quantity) > $product->quantity) {
            return redirect()->back()->with('error', 'Cannot add more items. Only ' . $product->quantity . ' available in stock.');
        }

        // Check if mixing bookable and non-bookable items
        $cartItems = \Cart::getContent();
        $isProductBookable = (bool) $product->is_bookable;
        
        if ($cartItems->count() > 0) {
            // Check the bookable status of existing cart items
            $hasBookableItems = false;
            $hasRegularItems = false;
            
            foreach ($cartItems as $item) {
                $isBookable = false;
                if (isset($item->attributes->is_bookable)) {
                    $isBookable = (bool) $item->attributes->is_bookable;
                } else {
                    // Fallback to database check
                    $existingProduct = Product::find($item->id);
                    $isBookable = $existingProduct && (bool) $existingProduct->is_bookable;
                }
                
                if ($isBookable) {
                    $hasBookableItems = true;
                } else {
                    $hasRegularItems = true;
                }
            }
            
            // Check if trying to mix
            if ($isProductBookable && $hasRegularItems) {
                return redirect()->back()->with('error', 'Cannot add a pre-order item to cart with in-stock items. Please clear your cart or complete your current order first.');
            }
            
            if (!$isProductBookable && $hasBookableItems) {
                return redirect()->back()->with('error', 'Cannot add an in-stock item to cart with pre-order items. Please clear your cart or complete your current order first.');
            }
        }

        // Calculate price: if bookable, customer pays (price - 200) + delivery
        $itemPrice = $product->display_price ?? 0;
        if ($product->is_bookable && $itemPrice > 200) {
            $itemPrice = $itemPrice - 200; // Deduct booking amount
        }

        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $itemPrice,
            'quantity' => $quantity,
            'attributes' => [
                'image' => $product->image,
                'category' => $product->category_name,
                'slug' => $product->id,
                'is_bookable' => (bool) $product->is_bookable, // Explicitly convert to boolean
                'original_price' => $product->display_price ?? 0,
            ]
        ]);

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function update(Request $request, $itemId)
    {
        $quantity = $request->input('quantity', 1);
        
        $cartItem = \Cart::get($itemId);
        if (!$cartItem) {
            return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
        }

        $product = Product::find($itemId);
        if (!$product || !$product->is_active) {
            return redirect()->route('cart.index')->with('error', 'Product is no longer available.');
        }

        if ($quantity > $product->quantity) {
            return redirect()->route('cart.index')->with('error', 'Insufficient stock. Only ' . $product->quantity . ' available.');
        }

        \Cart::update($itemId, [
            'quantity' => [
                'relative' => false,
                'value' => $quantity
            ],
        ]);

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully!');
    }

    public function remove($itemId)
    {
        \Cart::remove($itemId);
        
        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        \Cart::clear();
        
        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully.');
    }
}
