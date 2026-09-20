<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

/**
 * When an order holds stock, and moving it when that changes.
 *
 * Stock used to come off the moment an order was placed, so a pending order
 * nobody had accepted yet was already reducing what the shop could sell. It now
 * comes off when the order is confirmed and goes back when it is cancelled.
 *
 * The order's status is the whole source of truth - there is no "already
 * deducted" flag to drift out of step with it. An order in a holding status has
 * its stock taken; an order in any other status does not. Every transition is
 * therefore idempotent: moving between two holding statuses (confirmed to
 * processing, say) moves nothing.
 */
class OrderStockService
{
    /**
     * Statuses where the goods are committed to the customer. Pending is not
     * one: the order is accepted but the stock is still sellable. Cancelled is
     * not one either, which is what puts the units back.
     */
    public const HOLDING_STATUSES = ['confirmed', 'processing', 'shipped', 'delivered'];

    public static function holdsStock(?string $status): bool
    {
        return in_array($status, self::HOLDING_STATUSES, true);
    }

    /**
     * Move stock only when an order crosses between holding and not holding.
     */
    public function applyStatusChange(Order $order, ?string $oldStatus, string $newStatus): void
    {
        $held = self::holdsStock($oldStatus);
        $holds = self::holdsStock($newStatus);

        if ($held === $holds) {
            return;
        }

        $holds ? $this->commit($order) : $this->release($order);
    }

    /**
     * Take this order's items out of stock.
     *
     * Deliberately allows the result to go negative rather than refusing: by
     * the time an order is being confirmed the goods are already spoken for,
     * and blocking the status change would leave the admin unable to record
     * what actually happened. A negative figure is visible and fixable; a
     * silently dropped confirmation is not.
     */
    public function commit(Order $order): void
    {
        foreach ($this->stockRows($order) as [$row, $quantity]) {
            $row->decrement('quantity', $quantity);
        }
    }

    /** Put this order's items back into stock. */
    public function release(Order $order): void
    {
        foreach ($this->stockRows($order) as [$row, $quantity]) {
            $row->increment('quantity', $quantity);
        }
    }

    /**
     * The row that actually holds each item's stock, with the quantity to move.
     *
     * A line with a variant is held by the variant - the product's own quantity
     * column is 0 by design on a merged product, so returning stock there put
     * it somewhere nothing reads.
     *
     * @return array<int, array{0: Model, 1: int}>
     */
    private function stockRows(Order $order): array
    {
        $order->loadMissing('items.product', 'items.productVariant');

        $rows = [];

        foreach ($order->items as $item) {
            $row = $item->productVariant ?? $item->product;

            // The product may have been deleted out from under the order.
            if ($row) {
                $rows[] = [$row, (int) $item->quantity];
            }
        }

        return $rows;
    }
}
