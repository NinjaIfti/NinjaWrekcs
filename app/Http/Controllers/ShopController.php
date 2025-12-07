<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category', '');
        
        // Available categories
        $categories = [
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
        ];
        
        // Fetch products from database
        $query = Product::where('is_active', true);
        
        if ($category && in_array($category, array_keys($categories))) {
            $query->where('category', $category);
        }
        
        $products = $query->latest()->get();
        
        return view('shop.index', [
            'selectedCategory' => $category,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function show(Product $product): View
    {
        // Only show active products
        if (!$product->is_active) {
            abort(404);
        }
        
        return view('shop.show', compact('product'));
    }
}
