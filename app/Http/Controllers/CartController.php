<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = \Cart::getContent();
        
        // Recalculate subtotal using original prices for pre-order items
        $cartSubTotal = 0;
        $hasBookableItems = false;
        
        foreach ($cartItems as $item) {
            $productId = is_string($item->id) && str_contains($item->id, '_') ? (int) explode('_', $item->id)[0] : $item->id;
            $product = Product::find($productId);
            $isBookable = false;
            if (isset($item->attributes->is_bookable)) {
                $isBookable = (bool) $item->attributes->is_bookable;
            } else {
                $isBookable = $product && (bool) $product->is_bookable;
            }
            if ($isBookable) {
                $hasBookableItems = true;
                if ($product) {
                    $originalPrice = (float) ($product->display_price ?? $product->price ?? 0);
                    $cartSubTotal += $originalPrice * $item->quantity;
                } else {
                    $originalPrice = (float) ($item->attributes->original_price ?? $item->price);
                    $cartSubTotal += $originalPrice * $item->quantity;
                }
            } else {
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

        $variantId = $request->input('variant_id');
        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::where('product_id', $product->id)->find($variantId);
            if (!$variant) {
                return redirect()->back()->with('error', 'Invalid variant selected.');
            }
        }

        $itemPrice = $variant ? (float) $variant->price : ($product->display_price ?? 0);
        if (!$variant && ($product->price_tba || $product->price == 0 || $itemPrice == 0 || !$itemPrice)) {
            return redirect()->back()->with('error', 'Price will be announced later. Please check back once pricing is available.');
        }

        $quantity = $request->input('quantity', 1);
        if ($product->quantity < $quantity) {
            return redirect()->back()->with('error', 'Insufficient stock. Only ' . $product->quantity . ' available.');
        }

        $cartId = $variant ? $product->id . '_' . $variant->id : $product->id;
        $cartItem = \Cart::get($cartId);
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
                    // Parse composite ids like "12_3" (product_variant) before DB lookup
                    $existingProductId = is_string($item->id) && str_contains($item->id, '_')
                        ? (int) explode('_', $item->id)[0]
                        : $item->id;
                    $existingProduct = Product::find($existingProductId);
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

        if (!$variant && $product->is_bookable && $itemPrice > 200) {
            $itemPrice = $itemPrice - 200;
        }

        $imagePath = $variant && $variant->images->isNotEmpty()
            ? $variant->images->first()->path
            : ($product->cover_photo ?? $product->image);
        $displayName = $variant ? $product->name . ' — ' . $variant->name : $product->name;

        \Cart::add([
            'id' => $cartId,
            'name' => $displayName,
            'price' => $itemPrice,
            'quantity' => $quantity,
            'attributes' => [
                'image' => $imagePath,
                'category' => $product->category_name,
                'slug' => $product->slug ?? $product->id,
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'variant_name' => $variant?->name,
                'is_bookable' => (bool) $product->is_bookable,
                'original_price' => $variant ? (float) $variant->price : ($product->display_price ?? 0),
            ]
        ]);

        return redirect()->back()->with('success', 'Product added to cart successfully!');
    }

    public function update(Request $request, $itemId)
    {
        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < 1) {
            return redirect()->route('cart.index')->with('error', 'Quantity must be at least 1.');
        }
        $cartItem = \Cart::get($itemId);
        if (!$cartItem) {
            return redirect()->route('cart.index')->with('error', 'Item not found in cart.');
        }
        $productId = is_string($itemId) && str_contains($itemId, '_') ? (int) explode('_', $itemId)[0] : $itemId;
        $product = Product::find($productId);
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
