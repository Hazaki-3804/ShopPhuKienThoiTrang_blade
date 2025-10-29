<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use App\Models\Cart;

class PayosController extends Controller
{
    public function paymentSuccess(Request $request)
    {
        $data = $request->all();
        $orderId = (int)($data['orderCode'] ?? 0);
        $amount = Order::find($orderId)->total_price;
        $transactionCode = (string)($data['transactionId'] ?? ($data['id'] ?? ($data['paymentLinkId'] ?? '')));
        $status = strtoupper((string)($data['status'] ?? ''));
        if (Auth::check() && $orderId > 0) {
            $order = Order::with('order_items')->find($orderId);
            if ($order && $order->user_id === Auth::id()) {
                $cart = Cart::where('user_id', Auth::id())->first();
                if ($cart) {
                    $productIds = $order->order_items->pluck('product_id')->all();
                    if (!empty($productIds)) {
                        CartItem::where('cart_id', $cart->id)
                            ->whereIn('product_id', $productIds)
                            ->delete();
                    }
                    // Nếu giỏ hàng trống sau khi xóa các sản phẩm đã mua thì xóa luôn giỏ
                    $remaining = CartItem::where('cart_id', $cart->id)->count();
                    if ($remaining === 0) {
                        $cart->delete();
                    }
                }
            }
        }
        if ($status === 'PAID') {        
            //Lưu vào payment
            Payment::create([
                'order_id' => $orderId,
                'amount' => $amount,
                'status' => 'completed',
                'payment_method' => 'payos',
                'transaction_code' => $transactionCode,
                'paid_at' => now(),
            ]);
        }
        // Xóa chỉ những sản phẩm vừa mua ra khỏi giỏ hàng
        if (Auth::check() && $orderId > 0) {
            $order = Order::with('order_items')->find($orderId);
            if ($order && $order->user_id === Auth::id()) {
                $cart = Cart::where('user_id', Auth::id())->first();
                if ($cart) {
                    $productIds = $order->order_items->pluck('product_id')->all();
                    if (!empty($productIds)) {
                        CartItem::where('cart_id', $cart->id)
                            ->whereIn('product_id', $productIds)
                            ->delete();
                    }
                    // Nếu giỏ hàng trống sau khi xóa các sản phẩm đã mua thì xóa luôn giỏ
                    $remaining = CartItem::where('cart_id', $cart->id)->count();
                    if ($remaining === 0) {
                        $cart->delete();
                    }
                }
            }
        }
        
        // Xóa session buy_now_item nếu có
        session()->forget('buy_now_item');
        
        return redirect()->route('shop.index')->with(['success'=>true, 'message'=>'Thanh toán qua PayOS thành công']);
    }
    public function paymentCancel()
    {
        return redirect()->route('shop.index')->with(['success'=>false, 'message'=>'Thanh toán qua PayOS thất bại']);
    }
    public function handlePayOSWebhook(Request $request)
    {
        $body = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                "error" => 1,
                "message" => "Invalid JSON payload"
            ], 400);
        }

        // Handle webhook test
        if (in_array($body["data"]["description"], ["Ma giao dich thu nghiem", "VQRIO123"])) {
            return response()->json([
                "error" => 0,
                "message" => "Ok",
                "data" => $body["data"]
            ]);
        }

        try {
            $this->payOS->verifyPaymentWebhookData($body);
        } catch (\Exception $e) {
            return response()->json([
                "error" => 1,
                "message" => "Invalid webhook data",
                "details" => $e->getMessage()
            ], 400);
        }

        // Process webhook data -> Save payment when status is paid

    }
}