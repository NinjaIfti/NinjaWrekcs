{{--
    The <option> rows for one product on an admin order form.

    A variant product has no price or stock of its own - both belong to the
    variants - so it contributes one option per active variant rather than one
    for itself. Every option carries the price and stock the line will actually
    use, and its variant id, which the form's JS copies into the hidden
    variant_id beside the select.

    $product     the product to render
    $inStockOnly hide options with no stock (creating), or show them (editing,
                 where an existing line must stay selectable once sold out)
--}}
@php
    $pricing = app(\App\Services\PricingService::class);
    $inStockOnly = $inStockOnly ?? true;
@endphp

@if($product->hasVariants())
    @foreach($product->variants->where('is_active', true) as $variant)
        @php
            $stock = $product->availableStock($variant);
            $price = $pricing->priceFor($product, $variant);
            $compareAt = $pricing->compareAtPriceFor($product, $variant);
        @endphp
        @if(! $inStockOnly || $stock > 0)
            <option value="{{ $product->id }}"
                    data-variant-id="{{ $variant->id }}"
                    data-price="{{ $price }}"
                    data-original-price="{{ $compareAt ?? $price }}"
                    data-stock="{{ $stock }}"
                    data-has-deal="{{ $compareAt !== null ? '1' : '0' }}">
                {{ $product->name }} — {{ $variant->name }}
                - ৳{{ number_format($price, 2) }}@if($compareAt !== null) (Deal: was ৳{{ number_format($compareAt, 2) }})@endif
                (Stock: {{ $stock }})
            </option>
        @endif
    @endforeach
@else
    @php
        $stock = $product->availableStock();
        $price = $pricing->priceFor($product);
        $compareAt = $pricing->compareAtPriceFor($product);
    @endphp
    @if(! $inStockOnly || $stock > 0)
        <option value="{{ $product->id }}"
                data-variant-id=""
                data-price="{{ $price }}"
                data-original-price="{{ $compareAt ?? $price }}"
                data-stock="{{ $stock }}"
                data-has-deal="{{ $compareAt !== null ? '1' : '0' }}">
            {{ $product->name }}
            - ৳{{ number_format($price, 2) }}@if($compareAt !== null) (Deal: was ৳{{ number_format($compareAt, 2) }})@endif
            (Stock: {{ $stock }})
        </option>
    @endif
@endif
