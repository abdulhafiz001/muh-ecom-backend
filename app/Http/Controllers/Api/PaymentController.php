<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    private $paystackSecretKey;
    private $paystackPublicKey;
    private $paystackUrl;

    public function __construct()
    {
        $this->paystackSecretKey = config('paystack.secretKey');
        $this->paystackPublicKey = config('paystack.publicKey');
        $this->paystackUrl = config('paystack.paymentUrl');
    }

    /**
     * Initialize payment with Paystack
     */
    public function initializePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'email' => 'required|email',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::with('orderItems.product')->findOrFail($request->order_id);
            
            // Verify order amount matches request amount
            if ($order->total != $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order amount mismatch'
                ], 400);
            }

            // Generate unique reference
            $reference = 'PAY_' . time() . '_' . Str::random(10);

            // Prepare payment data
            $paymentData = [
                'email' => $request->email,
                'amount' => $order->total * 100, // Convert to kobo
                'reference' => $reference,
                'currency' => 'NGN',
                'callback_url' => config('app.url') . '/api/payment/callback',
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user->first_name . ' ' . $order->user->last_name,
                ]
            ];

            // Initialize payment with Paystack
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->post($this->paystackUrl . '/transaction/initialize', $paymentData);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status']) {
                // Update order with payment reference
                $order->update([
                    'payment_reference' => $reference,
                    'payment_status' => 'pending'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment initialized successfully',
                    'data' => [
                        'authorization_url' => $responseData['data']['authorization_url'],
                        'access_code' => $responseData['data']['access_code'],
                        'reference' => $reference,
                        'public_key' => $this->paystackPublicKey
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initialize payment',
                    'error' => $responseData['message'] ?? 'Unknown error'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify payment with Paystack
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verify payment with Paystack
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
            ])->get($this->paystackUrl . '/transaction/verify/' . $request->reference);

            $responseData = $response->json();

            if ($response->successful() && $responseData['status']) {
                $paymentData = $responseData['data'];
                
                // Find order by payment reference
                $order = Order::where('payment_reference', $request->reference)->first();
                
                if (!$order) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Order not found'
                    ], 404);
                }

                // Check if payment was successful
                if ($paymentData['status'] === 'success') {
                    DB::beginTransaction();
                    
                    try {
                        // Update order status
                        $order->update([
                            'payment_status' => 'completed',
                            'status' => 'confirmed',
                            'payment_method' => 'paystack',
                            'payment_reference' => $paymentData['reference'],
                            'payment_authorization_code' => $paymentData['authorization']['authorization_code'] ?? null,
                            'payment_customer_code' => $paymentData['customer']['customer_code'] ?? null,
                        ]);

                        // Update product stock quantities
                        foreach ($order->orderItems as $orderItem) {
                            $product = $orderItem->product;
                            $product->decrement('stock_quantity', $orderItem->quantity);
                        }

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Payment verified successfully',
                            'data' => [
                                'order' => $order->load('orderItems.product'),
                                'payment' => $paymentData
                            ]
                        ]);

                    } catch (\Exception $e) {
                        DB::rollback();
                        throw $e;
                    }
                } else {
                    // Payment failed
                    $order->update([
                        'payment_status' => 'failed'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Payment verification failed',
                        'data' => [
                            'status' => $paymentData['status'],
                            'gateway_response' => $paymentData['gateway_response']
                        ]
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed',
                    'error' => $responseData['message'] ?? 'Unknown error'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Paystack webhook
     */
    public function handleWebhook(Request $request)
    {
        $input = $request->all();
        $hash = $request->header('x-paystack-signature');

        // Verify webhook signature
        if (!$this->verifyWebhookSignature($input, $hash)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $event = $input['event'];

        if ($event === 'charge.success') {
            $paymentData = $input['data'];
            $reference = $paymentData['reference'];

            $order = Order::where('payment_reference', $reference)->first();
            
            if ($order && $order->payment_status !== 'completed') {
                DB::beginTransaction();
                
                try {
                    $order->update([
                        'payment_status' => 'completed',
                        'status' => 'confirmed',
                        'payment_method' => 'paystack',
                        'payment_authorization_code' => $paymentData['authorization']['authorization_code'] ?? null,
                        'payment_customer_code' => $paymentData['customer']['customer_code'] ?? null,
                    ]);

                    // Update product stock quantities
                    foreach ($order->orderItems as $orderItem) {
                        $product = $orderItem->product;
                        $product->decrement('stock_quantity', $orderItem->quantity);
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    throw $e;
                }
            }
        }

        return response()->json(['message' => 'Webhook processed']);
    }

    /**
     * Verify webhook signature
     */
    private function verifyWebhookSignature($input, $signature)
    {
        $expectedSignature = hash_hmac('sha512', json_encode($input), $this->paystackSecretKey);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::where('payment_reference', $request->reference)->first();
            
            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->status,
                    'total' => $order->total
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
