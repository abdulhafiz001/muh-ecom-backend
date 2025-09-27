<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix double-encoded addresses in existing orders
        $orders = \App\Models\Order::all();
        
        foreach ($orders as $order) {
            // Check if shipping_address is double-encoded (starts with quote)
            if (is_string($order->shipping_address) && str_starts_with($order->shipping_address, '"')) {
                $decoded = json_decode($order->shipping_address, true);
                if ($decoded) {
                    $order->shipping_address = $decoded;
                }
            }
            
            // Check if billing_address is double-encoded
            if (is_string($order->billing_address) && str_starts_with($order->billing_address, '"')) {
                $decoded = json_decode($order->billing_address, true);
                if ($decoded) {
                    $order->billing_address = $decoded;
                }
            }
            
            $order->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed
        // The addresses would need to be re-encoded as JSON strings
    }
};
