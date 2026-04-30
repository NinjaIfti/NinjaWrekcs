<?php

namespace App\Http\Controllers;

use App\Models\GiveawayEntry;
use App\Models\Order;
use App\Services\MimsmsService;
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

        $phone = trim((string) $request->input('phone', ''));
        $phoneCore = $this->phoneCore($phone);

        $orders = Order::query()
            ->where('is_deleted', false)
            ->where('status', 'delivered')
            ->latest()
            ->get()
            ->filter(function (Order $order) use ($phoneCore) {
                return $this->phoneCore((string) $order->phone) === $phoneCore;
            })
            ->values();

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

    public function manualStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $normalizedPhone = MimsmsService::normalizePhone($validated['phone']);
        $phoneCore = $this->phoneCore($normalizedPhone);
        if ($phoneCore === '') {
            return back()->with('warning', 'Invalid phone number format.');
        }

        GiveawayEntry::create([
            'order_id' => null,
            'phone' => $normalizedPhone,
            'invoice_number' => 'MANUAL-' . now()->format('YmdHis'),
            'order_date' => now(),
        ]);

        return back()->with('success', 'Manual phone entry added.');
    }

    private function phoneCore(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '880') && strlen($digits) >= 13) {
            return substr($digits, -10);
        }

        if (str_starts_with($digits, '0') && strlen($digits) >= 11) {
            return substr($digits, -10);
        }

        if (strlen($digits) >= 10) {
            return substr($digits, -10);
        }

        return '';
    }
}
