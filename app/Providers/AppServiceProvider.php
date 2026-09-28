<?php

namespace App\Providers;

use App\Models\ProductVariant;
use App\Observers\ProductVariantObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // A variant product's stock total is derived from its options rather
        // than typed in, so it has to follow every change to them.
        ProductVariant::observe(ProductVariantObserver::class);
    }
}
