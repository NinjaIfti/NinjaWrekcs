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
        $cartTotal = \Cart::getTotal();
        $cartSubTotal = \Cart::getSubTotal();
        
        return view('cart.index', compact('cartItems', 'cartTotal', 'cartSubTotal'));
    }

    public function add(Request $request, Product $product)
    {
        // Check if product is active and in stock
        if (!$product->is_active) {
            return redirect()->back()->with('error', 'This product is not available.');
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

        \Cart::add([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->display_price ?? 0,
            'quantity' => $quantity,
            'attributes' => [
                'image' => $product->image,
                'category' => $product->category_name,
                'slug' => $product->id,
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
