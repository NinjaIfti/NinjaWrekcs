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

        // Entry is automatic now, so "entered" means the order meets the published
        // rules rather than that someone clicked a button.
        $enteredOrderIds = $orders
            ->filter(fn (Order $order) => GiveawayEntry::orderQualifies($order))
            ->pluck('id')
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

        // Qualifying orders are entered automatically, so there is nothing to create
        // here — report the order's standing against the published rules instead.
        if (GiveawayEntry::orderQualifies($order)) {
            return back()->with('success', 'This order is already entered in the giveaway automatically.');
        }

        if ($order->created_at < \Carbon\Carbon::parse(GiveawayEntry::STARTS_AT)) {
            return back()->with('warning', 'Only orders placed from 1 August are eligible for this giveaway.');
        }

        return back()->with('warning', 'Orders must be at least ৳' . number_format(GiveawayEntry::MIN_ORDER_TOTAL) . ' to qualify for this giveaway.');
    }

    public function adminIndex(): View
    {
        // Automatic entries, derived live from orders. Nothing is stored, so an order
        // that gets cancelled or un-delivered simply stops appearing here.
        $auto = GiveawayEntry::qualifyingOrders()
            ->latest()
            ->get()
            ->map(fn (Order $order) => (object) [
                'source' => 'auto',
                'entry_id' => null,
                'phone' => $order->phone,
                'invoice_number' => 'INV-' . $order->id,
                'order_date' => $order->created_at,
                'entered_at' => $order->updated_at,
                'order_total' => $order->total,
            ]);

        // Manually added phone numbers still live in the table.
        $manual = GiveawayEntry::query()
            ->whereNull('order_id')
            ->latest()
            ->get()
            ->map(fn (GiveawayEntry $entry) => (object) [
                'source' => 'manual',
                'entry_id' => $entry->id,
                'phone' => $entry->phone,
                'invoice_number' => $entry->invoice_number,
                'order_date' => $entry->order_date,
                'entered_at' => $entry->created_at,
                'order_total' => null,
            ]);

        return view('admin.giveaway', [
            'entries' => $auto->concat($manual)->values(),
            'autoCount' => $auto->count(),
            'manualCount' => $manual->count(),
            'startsAt' => \Carbon\Carbon::parse(GiveawayEntry::STARTS_AT),
            'minTotal' => GiveawayEntry::MIN_ORDER_TOTAL,
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

    public function destroy(GiveawayEntry $entry): RedirectResponse
    {
        $entry->delete();

        return back()->with('success', 'Giveaway entry deleted.');
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
