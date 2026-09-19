<?php

namespace App\Http\Controllers;

use App\Exceptions\CartException;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index()
    {
        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'summary' => $this->cart->summary(),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $variant = null;

        if ($variantId = $request->input('variant_id')) {
            $variant = ProductVariant::where('product_id', $product->id)->find($variantId);

            if (! $variant) {
                return back()->with('error', 'Invalid variant selected.');
            }
        }

        $quantity = max(1, (int) $request->input('quantity', 1));

        try {
            $this->cart->add($product, $variant, $quantity);
        } catch (CartException $e) {
            return back()->with('error', $e->getMessage());
        }

        $line = $this->cart->lines()->firstWhere('id', $this->cart->keyFor($product, $variant));

        return back()
            ->with('success', 'Product added to cart successfully!')
            ->with('data_layer_event', [
                'event' => 'add_to_cart',
                'ecommerce' => [
                    'currency' => 'BDT',
                    'value' => $line ? $line->unitPrice * $quantity : 0.0,
                    'items' => [[
                        'item_id' => (string) $product->id,
                        'item_name' => $line?->name ?? $product->name,
                        'item_category' => $product->category_name ?? 'Valorant Collectibles',
                        'price' => $line?->unitPrice ?? 0.0,
                        'quantity' => $quantity,
                    ]],
                ],
            ]);
    }

    public function update(Request $request, string $itemId)
    {
        try {
            $this->cart->update($itemId, (int) $request->input('quantity', 1));
        } catch (CartException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully!');
    }

    public function remove(string $itemId)
    {
        $this->cart->remove($itemId);

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        $this->cart->clear();

        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully.');
    }
}
