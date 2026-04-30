<?php

namespace App\Http\Controllers;

use App\Models\GiveawayEntry;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiveawayController extends Controller
{
    public function page(): View
    {
        return view('agent-code');
    }

    public function search(Request $request): View
    {
        $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $phone = trim($request->string('phone')->toString());

        $orders = Order::query()
            ->where('is_deleted', false)
            ->where('status', 'delivered')
            ->where('phone', 'like', '%' . $phone . '%')
            ->latest()
            ->get();

        $enteredOrderIds = GiveawayEntry::query()
            ->whereIn('order_id', $orders->pluck('id'))
            ->pluck('order_id')
            ->all();

        return view('agent-code', [
            'searchPhone' => $phone,
            'orders' => $orders,
            'enteredOrderIds' => $enteredOrderIds,
        ]);
    }

    public function enter(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::query()
            ->where('id', $validated['order_id'])
            ->where('is_deleted', false)
            ->where('status', 'delivered')
            ->firstOrFail();

        $existing = GiveawayEntry::query()->where('order_id', $order->id)->first();
        if ($existing) {
            return back()->with('warning', 'This order is already entered in giveaway.');
        }

        GiveawayEntry::create([
            'order_id' => $order->id,
            'phone' => $order->phone,
            'invoice_number' => 'INV-' . $order->id,
            'order_date' => $order->created_at,
        ]);

        return back()->with('success', 'Entry added successfully.');
    }

    public function adminIndex(): View
    {
        $entries = GiveawayEntry::query()
            ->latest()
            ->get();

        return view('admin.giveaway', [
            'entries' => $entries,
        ]);
    }
}
