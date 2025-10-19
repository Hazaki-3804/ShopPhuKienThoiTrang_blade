<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments.index');
    }
    public function showPaymentQR($orderId)
    {
        $order = Order::findOrFail($orderId);
        $bank = '970415'; // VietinBank BIN
        $account = '123456789';
        $amount = $order->total_amount;
        $content = urlencode("Thanh toan don hang #{$order->id}");
        $qrUrl = "https://img.vietqr.io/image/{$bank}-{$account}-compact2.png?amount={$amount}&addInfo={$content}";

        return view('payment.qr', compact('order', 'qrUrl'));
    }
}
