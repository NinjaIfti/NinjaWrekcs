<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $category = $request->query('category', '');
        $search = $request->query('search', '');
        $minPrice = $request->query('min_price', '');
        $maxPrice = $request->query('max_price', '');
        $sort = $request->query('sort', 'newest');
        $inStock = $request->query('in_stock', '');
        $perPage = $request->query('per_page', 12);
        
        // Available categories
        $categories = [
            'figures' => 'Agent Figures',
            'knives' => 'Knives & Weapons',
            'stickers' => 'Stickers & Keychains',
        ];
        
        // Get category counts
        $categoryCounts = [];
        foreach (array_keys($categories) as $cat) {
            $categoryCounts[$cat] = Product::where('is_active', true)->where('category', $cat)->count();
        }
        
        // Fetch products from database
        $query = Product::with('images')->where('is_active', true);
        
        // Search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }
        
        // Category filter
        if ($category && in_array($category, array_keys($categories))) {
            $query->where('category', $category);
        }
        
        // Price range filter
        if ($minPrice !== '' && is_numeric($minPrice)) {
            $query->where('price', '>=', $minPrice);
        }
        
        if ($maxPrice !== '' && is_numeric($maxPrice)) {
            $query->where('price', '<=', $maxPrice);
        }
        
        // In stock filter
        if ($inStock) {
            $query->where('quantity', '>', 0);
        }
        
        // Sorting
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'popular':
                $query->orderBy('reviews', 'desc')->orderBy('rating', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }
        
        // Use pagination for infinite scroll
        $products = $query->paginate($perPage);
        
        // Get price range for filter
        $priceRange = Product::where('is_active', true)->selectRaw('MIN(price) as min, MAX(price) as max')->first();
        
        // For AJAX requests (infinite scroll), return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('shop.partials.product-grid', ['products' => $products])->render(),
                'hasMore' => $products->hasMorePages(),
                'nextPage' => $products->currentPage() + 1,
            ]);
        }
        
        return view('shop.index', [
            'selectedCategory' => $category,
            'categories' => $categories,
            'categoryCounts' => $categoryCounts,
            'products' => $products,
            'search' => $search,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'sort' => $sort,
            'inStock' => $inStock,
            'priceRange' => $priceRange,
        ]);
    }

    public function show(Product $product): View
    {
        // Only show active products
        if (!$product->is_active) {
            abort(404);
        }
        
        $product->load('images');

        return view('shop.show', compact('product'));
    }
}
