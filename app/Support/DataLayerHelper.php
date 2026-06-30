<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Collection;

class DataLayerHelper
{
    public static function hash(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return hash('sha256', strtolower(trim($value)));
    }

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '880')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '880' . substr($digits, 1);
        }

        return '880' . $digits;
    }

    /**
     * @return array{first: string, last: string}
     */
    public static function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', strtolower(trim($name))) ?: [];

        return [
            'first' => $parts[0] ?? '',
            'last' => count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '',
        ];
    }

    /**
     * @param  Collection<int, mixed>  $cartItems
     * @return list<array<string, mixed>>
     */
    public static function cartItemsPayload(Collection $cartItems): array
    {
        return $cartItems->map(function ($item) {
            $productId = $item->attributes->product_id ?? (
                is_string($item->id) && str_contains($item->id, '_')
                    ? (int) explode('_', $item->id)[0]
                    : $item->id
            );

            return [
                'item_id' => (string) $productId,
                'item_name' => $item->name,
                'item_category' => $item->attributes->category ?? 'Valorant Collectibles',
                'price' => (float) $item->price,
                'quantity' => (int) $item->quantity,
            ];
        })->values()->all();
    }

    /**
     * @param  Collection<int, mixed>  $cartItems
     * @return array<string, mixed>
     */
    public static function beginCheckoutPayload(
        Collection $cartItems,
        float $cartSubTotal,
        bool $hasBookableItems,
        float $totalBookingAmount
    ): array {
        $payload = [
            'event' => 'begin_checkout',
            'ecommerce' => [
                'currency' => 'BDT',
                'value' => $cartSubTotal,
                'items' => self::cartItemsPayload($cartItems),
            ],
            'checkout' => [
                'item_count' => $cartItems->count(),
                'subtotal' => $cartSubTotal,
                'has_preorder' => $hasBookableItems,
                'booking_amount' => $totalBookingAmount,
                'estimated_shipping_inside_dhaka' => 80.0,
                'estimated_shipping_outside_dhaka' => 120.0,
            ],
        ];

        $userData = self::userDataPayload(
            auth()->user()?->email,
            auth()->user()?->phone,
            auth()->user()?->name,
            auth()->user()?->address,
            auth()->check()
        );

        if ($userData !== []) {
            $payload['user_data'] = $userData;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public static function purchasePayload(Order $order): array
    {
        $order->loadMissing('items');

        $purchaseItems = $order->items->map(fn ($item) => [
            'item_id' => (string) ($item->product_id ?? $item->id),
            'item_name' => $item->product_name,
            'price' => (float) $item->price,
            'quantity' => (int) $item->quantity,
        ])->values()->all();

        $coupon = $order->coupon_code ?: null;

        $payload = [
            'event' => 'purchase',
            'ecommerce' => array_filter([
                'transaction_id' => (string) $order->id,
                'value' => (float) $order->total,
                'currency' => 'BDT',
                'tax' => 0.0,
                'shipping' => (float) $order->delivery_charge,
                'coupon' => $coupon,
                'items' => $purchaseItems,
            ], fn ($value) => $value !== null && $value !== ''),
            'checkout' => [
                'subtotal' => (float) $order->subtotal,
                'discount' => (float) $order->discount,
                'coupon_discount' => (float) ($order->coupon_discount ?? 0),
                'delivery_charge' => (float) $order->delivery_charge,
                'delivery_location' => $order->delivery_location,
                'payment_method' => $order->payment_method,
                'is_preorder_booking' => (bool) $order->is_preorder_booking,
                'booking_amount' => (float) ($order->booking_amount ?? 0),
                'order_status' => $order->status,
                'item_count' => $order->items->count(),
            ],
        ];

        $userData = self::userDataPayload(
            $order->email,
            $order->phone,
            $order->name,
            $order->address,
            true,
            $order->delivery_location
        );

        if ($userData !== []) {
            $payload['user_data'] = $userData;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public static function userDataPayload(
        ?string $email,
        ?string $phone,
        ?string $name,
        ?string $address = null,
        bool $includeWhenGuest = true,
        ?string $deliveryLocation = null
    ): array {
        if (!$includeWhenGuest && !auth()->check()) {
            return [];
        }

        $nameParts = self::splitName($name ?? '');
        $region = $deliveryLocation === 'inside_dhaka'
            ? 'dhaka'
            : ($deliveryLocation === 'outside_dhaka' ? 'bangladesh' : null);

        $addressPayload = array_filter([
            'first_name' => self::hash($nameParts['first'] ?: null),
            'last_name' => self::hash($nameParts['last'] ?: null),
            'street' => self::hash($address),
            'region' => self::hash($region),
            'country' => self::hash('bd'),
        ]);

        $payload = array_filter([
            'email_address' => self::hash($email),
            'phone_number' => $phone ? self::hash(self::normalizePhone($phone)) : null,
        ]);

        if ($addressPayload !== []) {
            $payload['address'] = $addressPayload;
        }

        return $payload;
    }
}
