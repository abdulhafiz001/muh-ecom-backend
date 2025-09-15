<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        // For now, we'll use a simple session-based approach
        // In production, you'd want to use proper authentication
        $userId = $request->user_id ?? 1; // Temporary user ID
        
        $cartItems = CartItem::with('product')
            ->where('user_id', $userId)
            ->get();

        $total = $cartItems->sum(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            return $item->quantity * $price;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'total' => $total
            ]
        ]);
    }

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // For now, use a default user ID if none provided
        $userId = $request->user_id ?? 1;
        
        // Check if product already in cart
        $existingItem = CartItem::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $request->quantity
            ]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity
            ]);
        }

        // Return the updated cart
        $cartItems = CartItem::with('product')
            ->where('user_id', $userId)
            ->get();

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * ($item->product->sale_price ?? $item->product->price);
        });

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'data' => [
                'items' => $cartItems,
                'total' => $total
            ]
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id ?? 1; // Temporary user ID
        
        $cartItem = CartItem::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated'
        ]);
    }

    public function remove($id, Request $request)
    {
        $userId = $request->user_id ?? 1; // Temporary user ID
        
        CartItem::where('user_id', $userId)
            ->where('id', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
    }

    public function clear(Request $request)
    {
        $userId = $request->user_id ?? 1; // Temporary user ID
        
        CartItem::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared'
        ]);
    }
}
