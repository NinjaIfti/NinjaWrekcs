<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>';
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Homepage
        $sitemap .= $this->addUrl(url('/'), '1.0', 'daily');
        
        // Static pages
        $sitemap .= $this->addUrl(route('shop.index'), '0.9', 'daily');
        $sitemap .= $this->addUrl(route('about'), '0.7', 'monthly');
        $sitemap .= $this->addUrl(route('contact'), '0.7', 'monthly');
        $sitemap .= $this->addUrl(route('faq'), '0.6', 'monthly');
        $sitemap .= $this->addUrl(route('terms'), '0.5', 'yearly');
        $sitemap .= $this->addUrl(route('shipping'), '0.6', 'monthly');
        
        // Products - dynamic
        $products = Product::where('is_active', true)->get();
        foreach ($products as $product) {
            $url = route('shop.show', $product->slug ?? $product->id);
            $sitemap .= $this->addUrl(
                $url,
                '0.8',
                'weekly',
                $product->updated_at->toAtomString()
            );
        }
        
        $sitemap .= '</urlset>';
        
        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }
    
    private function addUrl(string $loc, string $priority = '0.5', string $changefreq = 'monthly', ?string $lastmod = null): string
    {
        $url = '<url>';
        $url .= '<loc>' . htmlspecialchars($loc) . '</loc>';
        if ($lastmod) {
            $url .= '<lastmod>' . $lastmod . '</lastmod>';
        }
        $url .= '<changefreq>' . $changefreq . '</changefreq>';
        $url .= '<priority>' . $priority . '</priority>';
        $url .= '</url>';
        
        return $url;
    }
}
