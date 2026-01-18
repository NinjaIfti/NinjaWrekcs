<?php

namespace App\Http\Controllers;

use App\Models\StockNotification;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockNotificationController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        // Check if product is actually out of stock
        if ($product->quantity > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This product is currently in stock.'
            ]);
        }

        // Check if email is already registered for this product
        $existing = StockNotification::where('product_id', $request->product_id)
            ->where('email', $request->email)
            ->where('notified', false)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You are already subscribed to notifications for this product.'
            ]);
        }

        // Create notification request
        StockNotification::create([
            'product_id' => $request->product_id,
            'email' => $request->email,
            'notified' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You will be notified when this product is back in stock.'
        ]);
    }
}
