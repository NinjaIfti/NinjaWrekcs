<?php

namespace App\View\Components;

use App\Services\CartService;
use Illuminate\View\Component;
use Illuminate\View\View;

class CartSummary extends Component
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function render(): View
    {
        return view('components.cart-summary', [
            'summary' => $this->cart->summary(),
        ]);
    }
}
