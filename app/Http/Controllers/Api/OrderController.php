<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user_id ?? 1; // Get user ID from request
        
        $orders = Order::with('orderItems.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function getAllOrders(Request $request)
    {
        // Get all orders for admin use (no pagination)
        $orders = Order::with(['orderItems.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function getAllUsers(Request $request)
    {
        // Get all users for admin use
        $users = \App\Models\User::withCount('orders')
            ->withSum('orders', 'total')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function show($id, Request $request)
    {
        $userId = $request->user_id ?? 1; // Get user ID from request
        
        // Check if $id is numeric (order ID) or string (order number)
        if (is_numeric($id)) {
            $order = Order::with('orderItems.product')
                ->where('user_id', $userId)
                ->where('id', $id)
                ->first();
        } else {
            $order = Order::with('orderItems.product')
                ->where('user_id', $userId)
                ->where('order_number', $id)
                ->first();
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function destroy($id, Request $request)
    {
        $userId = $request->user_id ?? 1;
        
        $order = Order::where('user_id', $userId)->findOrFail($id);
        
        // Only allow deletion of pending/unpaid orders
        if ($order->payment_status === 'completed' || $order->status === 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete paid or confirmed orders'
            ], 400);
        }
        
        $order->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'shipping_address' => 'required',
            'billing_address' => 'required',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = $request->user_id ?? 1; // Get user ID from request
        
        // Get cart items
        $cartItems = CartItem::with('product')
            ->where('user_id', $userId)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = $cartItems->sum(function ($item) {
                $price = $item->product->sale_price ?? $item->product->price;
                return $item->quantity * $price;
            });
            
            $tax = $subtotal * 0.1; // 10% tax
            
            // Calculate shipping cost based on method
            $shippingMethod = $request->shipping_method ?? 'standard';
            $shippingCost = 0;
            switch ($shippingMethod) {
                case 'express':
                    $shippingCost = 7500.00; // 5000 base + 2500 extra
                    break;
                case 'overnight':
                    $shippingCost = 10000.00; // 5000 base + 5000 extra
                    break;
                default:
                    $shippingCost = 5000.00; // Standard shipping cost
            }
            
            $total = $subtotal + $tax + $shippingCost;

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'confirmed', // Set to confirmed since payment was successful
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'shipping_address' => $request->shipping_address,
                'billing_address' => $request->billing_address,
                'shipping_method' => $request->shipping_method ?? 'standard',
                'payment_method' => $request->payment_method ?? 'paystack',
                'payment_status' => 'completed', // Set to completed since payment was successful
                'payment_reference' => $request->payment_reference ?? null, // Add payment reference if provided
                'notes' => $request->notes,
            ]);

            // Generate order number
            $order->generateOrderNumber();

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->current_price,
                    'total' => $cartItem->quantity * $cartItem->product->current_price,
                ]);
            }

            // Clear cart
            CartItem::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $total
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
