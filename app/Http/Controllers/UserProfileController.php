<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserProfileController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $orders = $user->orders()->with('items')->latest()->get();
        
        return view('profile.index', compact('user', 'orders'));
    }

    public function updatePersonalInfo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'required|string|max:20',
        ]);

        Auth::user()->update($validated);

        return redirect()->route('profile.index')->with('success', 'Personal information updated successfully!');
    }

    public function updateAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address' => 'required|string',
        ]);

        Auth::user()->update($validated);

        return redirect()->route('profile.index')->with('success', 'Address updated successfully!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], Auth::user()->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.index')->with('success', 'Password updated successfully!');
    }

    public function showOrder(Order $order): View
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['items.product', 'user']);
        
        return view('profile.order-details', compact('order'));
    }
}
