<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $categoryId = $request->query('category_id', '');
        // Convert to integer if not empty, otherwise set to null
        $categoryId = !empty($categoryId) ? (int)$categoryId : null;
        $search = $request->query('search', '');
        $minPrice = $request->query('min_price', '');
        $maxPrice = $request->query('max_price', '');
        $sort = $request->query('sort', 'newest');
        $inStock = $request->query('in_stock', '');
        $perPage = $request->query('per_page', 12);
        
        // Get all categories from cache (cache for 1 hour)
        $categories = \Illuminate\Support\Facades\Cache::remember('shop_categories', 3600, function () {
            try {
                return \App\Models\Category::with(['children' => function($query) {
                        $query->where('is_active', true)->orderBy('order');
                    }])
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderByRaw('COALESCE(`order`, 999) ASC')
                    ->get();
            } catch (\Exception $e) {
                return \App\Models\Category::with(['children' => function($query) {
                        $query->where('is_active', true);
                    }])
                    ->whereNull('parent_id')
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->get();
            }
        });
        
        // Get category counts from cache (cache for 1 hour)
        $categoryCounts = \Illuminate\Support\Facades\Cache::remember('shop_category_counts', 3600, function () use ($categories) {
            $counts = [];
            foreach ($categories as $parentCategory) {
                $parentCount = Product::where('is_active', true)
                    ->where('category_id', $parentCategory->id)
                    ->count();
                
                if ($parentCategory->hasChildren()) {
                    foreach ($parentCategory->children as $childCategory) {
                        $counts[$childCategory->id] = Product::where('is_active', true)
                            ->where('category_id', $childCategory->id)
                            ->count();
                    }
                    $counts[$parentCategory->id] = $parentCount;
                } else {
                    $counts[$parentCategory->id] = $parentCount;
                }
            }
            return $counts;
        });
        
        // Build cache key based on filters (don't cache search results or filtered results)
        // Only cache default shop page (no filters, newest sort)
        $shouldCache = !$search && !$categoryId && $minPrice === '' && $maxPrice === '' && !$inStock && $sort === 'newest';
        $cacheKey = $shouldCache ? 'shop_products_page_' . $perPage . '_' . request()->get('page', 1) : null;
        
        if ($shouldCache && $cacheKey) {
            $products = \Illuminate\Support\Facades\Cache::remember($cacheKey, 1800, function () use ($perPage) {
                return Product::with('images', 'category')
                    ->where('is_active', true)
                    ->latest()
                    ->paginate($perPage);
            });
            $selectedCategoryModel = null;
        } else {
            // Fetch products from database (not cached when filters are applied)
            // Exclude products with null category_id when filtering by category
            $query = Product::with('images', 'category')->where('is_active', true);
            
            // If filtering by category, exclude products with null category_id
            if ($categoryId) {
                $query->whereNotNull('category_id');
            }
            
            // Initialize selected category model
            $selectedCategoryModel = null;
            
            // Search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }
            
            // Category filter
            if ($categoryId) {
                $selectedCategoryModel = \App\Models\Category::with(['children' => function($query) {
                    $query->where('is_active', true);
                }])->find($categoryId);
                
                if ($selectedCategoryModel) {
                    // Always include the selected category ID
                    $categoryIds = [$categoryId];
                    
                    // If it's a parent category with active children, include child category products too
                    if ($selectedCategoryModel->hasChildren() && $selectedCategoryModel->children->count() > 0) {
                        $childCategoryIds = $selectedCategoryModel->children->pluck('id')->toArray();
                        $categoryIds = array_merge($categoryIds, $childCategoryIds);
                    }
                    
                    // Query products from parent category AND any child categories
                    $query->whereIn('category_id', $categoryIds);
                } else {
                    // Category not found, show no products
                    $query->where('category_id', -1);
                }
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
        }
        
        // Get price range for filter (cache for 1 hour)
        $priceRange = \Illuminate\Support\Facades\Cache::remember('shop_price_range', 3600, function () {
            return Product::where('is_active', true)->selectRaw('MIN(price) as min, MAX(price) as max')->first();
        });
        
        // For AJAX requests (infinite scroll), return JSON
        if ($request->ajax() || $request->wantsJson()) {
            // Check if it's a mobile request
            $viewType = $request->query('view_type', 'desktop');
            $partial = $viewType === 'mobile' ? 'shop.partials.product-mobile' : 'shop.partials.product-grid';
            
            return response()->json([
                'html' => view($partial, ['products' => $products])->render(),
                'hasMore' => $products->hasMorePages(),
                'nextPage' => $products->currentPage() + 1,
            ]);
        }
        
        return view('shop.index', [
            'selectedCategoryId' => $categoryId,
            'selectedCategory' => $selectedCategoryModel,
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
        
        // Cache product details for 1 hour (only if not already loaded)
        if (!$product->relationLoaded('images')) {
            $cacheKey = 'product_' . $product->id;
            $product = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($product) {
                return $product->load('images');
            });
        }

        return view('shop.show', compact('product'));
    }

    public function recentPurchases()
    {
        // Cache recent purchases for 10 minutes
        $cacheKey = 'recent_purchases';
        
        $orderItem = \Illuminate\Support\Facades\Cache::remember($cacheKey, 600, function () {
            // Get a random recent order item from the last 7 days
            return OrderItem::whereHas('order', function($query) {
                    $query->where('created_at', '>=', now()->subDays(7))
                          ->where('status', '!=', 'cancelled');
                })
                ->with(['order', 'product'])
                ->inRandomOrder()
                ->first();
        });

        if (!$orderItem) {
            return response()->json([
                'success' => false,
                'message' => 'No recent purchases found'
            ]);
        }

        $order = $orderItem->order;
        $timeAgo = $this->getTimeAgo($order->created_at);
        
        // Extract first name from full name or use email username
        $customerName = $order->name ?? 'Someone';
        if ($customerName !== 'Someone') {
            $nameParts = explode(' ', $customerName);
            $customerName = $nameParts[0];
        }

        return response()->json([
            'success' => true,
            'purchase' => [
                'customer_name' => $customerName,
                'product_name' => $orderItem->product->name ?? 'a product',
                'time_ago' => $timeAgo,
                'location' => $order->address ? $this->extractCity($order->address) : null,
            ]
        ]);
    }

    private function getTimeAgo($datetime)
    {
        $now = now();
        $diff = $datetime->diffInMinutes($now);

        if ($diff < 60) {
            return $diff . ' min ago';
        } elseif ($diff < 1440) {
            $hours = floor($diff / 60);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($diff / 1440);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    private function extractCity($address)
    {
        // Try to extract city from address
        $cities = ['Dhaka', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh'];
        
        foreach ($cities as $city) {
            if (stripos($address, $city) !== false) {
                return $city;
            }
        }
        
        return null;
    }
}
