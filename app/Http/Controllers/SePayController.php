<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SePayController extends Controller
{
    /**
     * Tạo yêu cầu thanh toán SePay (giống VNPay)
     */
    public function createPayment(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $amount = $request->input('total');
            
            // Lấy thông tin cấu hình SePay
            $accountNumber = config('services.sepay.account_number');
            $accountName = config('services.sepay.account_name');
            $bankCode = config('services.sepay.bank_code');
            $bankName = config('services.sepay.bank_name');
            
            // Tạo mã giao dịch unique
            $transactionCode = 'SEPAY_' . $orderId . '_' . time();
            
            // Nội dung chuyển khoản
            $transferContent = 'NANGTHOSHOP ' . $orderId;
            
            // Tạo payment record với status pending
            Payment::create([
                'order_id' => $orderId,
                'amount' => $amount,
                'status' => 'pending',
                'payment_method' => 'sepay',
                'transaction_code' => $transactionCode,
            ]);
            
            // Lưu thông tin vào session để hiển thị
            session([
                'sepay_transaction' => [
                    'order_id' => $orderId,
                    'amount' => $amount,
                    'transaction_code' => $transactionCode,
                    'transfer_content' => $transferContent,
                    'account_number' => $accountNumber,
                    'account_name' => $accountName,
                    'bank_code' => $bankCode,
                    'bank_name' => $bankName,
                ]
            ]);
            
            // Hiển thị trang thông tin chuyển khoản
            return view('sepay.payment', ['transaction' => session('sepay_transaction')]);
            
        } catch (\Exception $e) {
            Log::error('SePay create payment error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['payment' => 'Có lỗi xảy ra khi tạo thanh toán SePay']);
        }
    }
    
    /**
     * Webhook callback từ SePay khi có giao dịch
     */
    public function callback(Request $request)
    {
        try {
            // Log toàn bộ request để debug
            Log::info('SePay webhook received', [
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);
            
            // Verify webhook signature (nếu có)
            $signature = $request->header('X-SePay-Signature');
            $webhookSecret = config('services.sepay.webhook_secret');
            
            if ($signature && $webhookSecret) {
                $payload = $request->getContent();
                $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
                
                if ($signature !== $expectedSignature) {
                    Log::warning('SePay webhook signature mismatch', [
                        'received' => $signature,
                        'expected' => $expectedSignature
                    ]);
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            }
            
            $data = $request->all();
            
            // Xử lý callback - hỗ trợ nhiều format từ SePay
            $isSuccess = false;
            $transferContent = '';
            $amount = 0;
            $transactionId = null;
            
            // Format 1: transaction_status
            if (isset($data['transaction_status']) && $data['transaction_status'] === 'success') {
                $isSuccess = true;
                $transferContent = $data['transfer_content'] ?? $data['content'] ?? '';
                $amount = $data['amount'] ?? 0;
                $transactionId = $data['transaction_id'] ?? $data['id'] ?? null;
            }
            // Format 2: status
            elseif (isset($data['status']) && in_array($data['status'], ['success', 'completed', 'SUCCESS'])) {
                $isSuccess = true;
                $transferContent = $data['content'] ?? $data['transfer_content'] ?? '';
                $amount = $data['amount'] ?? $data['transferAmount'] ?? 0;
                $transactionId = $data['id'] ?? $data['transactionId'] ?? null;
            }
            
            if ($isSuccess && $transferContent) {
                // Trích xuất order_id từ nội dung chuyển khoản
                // Format: NANGTHOSHOP {order_id}
                preg_match('/NANGTHOSHOP\s+(\d+)/', $transferContent, $matches);
                
                if (isset($matches[1])) {
                    $orderId = (int) $matches[1];
                    
                    // Kiểm tra xem payment đã tồn tại chưa
                    $existingPayment = Payment::where('order_id', $orderId)
                        ->where('payment_method', 'sepay')
                        ->first();
                    
                    if ($existingPayment && $existingPayment->status === 'pending') {
                        // Cập nhật payment hiện có
                        $existingPayment->update([
                            'status' => 'completed',
                            'transaction_code' => $transactionId,
                            'paid_at' => now(),
                        ]);
                        
                        Log::info('SePay payment updated for order: ' . $orderId);
                    } elseif (!$existingPayment) {
                        // Tạo payment record mới
                        Payment::create([
                            'order_id' => $orderId,
                            'amount' => $amount,
                            'status' => 'completed',
                            'payment_method' => 'sepay',
                            'transaction_code' => $transactionId,
                            'paid_at' => now(),
                        ]);
                        
                        Log::info('SePay payment created for order: ' . $orderId);
                    }
                    
                    // Cập nhật trạng thái đơn hàng
                    $order = Order::find($orderId);
                    if ($order && $order->status === 'pending') {
                        $order->update(['status' => 'processing']);
                    }
                }
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('SePay callback error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal error'], 500);
        }
    }
    
    /**
     * Return URL sau khi khách hàng hoàn tất chuyển khoản (giống VNPay)
     */
    public function returnPayment(Request $request)
    {
        $orderId = $request->query('order_id');
        $status = $request->query('status', 'pending'); // pending hoặc completed
        
        if (!$orderId) {
            return redirect()->route('home')->withErrors(['payment' => 'Không tìm thấy thông tin đơn hàng']);
        }
        
        // Cập nhật payment status nếu có
        if ($status === 'completed') {
            $payment = Payment::where('order_id', $orderId)
                ->where('payment_method', 'sepay')
                ->first();
            
            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                ]);
            }
            
            // Clear cart
            if (Auth::check()) {
                $cart = Cart::where('user_id', Auth::id())->first();
                if ($cart) {
                    CartItem::where('cart_id', $cart->id)->delete();
                    $cart->delete();
                }
            }
            
            // Xóa session
            session()->forget('sepay_transaction');
            
            return redirect()->route('user.orders.show', ['order' => $orderId])
                ->with('success', 'Thanh toán qua SePay thành công! Cảm ơn bạn đã mua hàng của Nàng Thơ Shop!');
        }
        
        // Trường hợp pending - chỉ xóa session
        session()->forget('sepay_transaction');
        
        return redirect()->route('user.orders.show', ['order' => $orderId])
            ->with('info', 'Đơn hàng của bạn đang chờ xác nhận thanh toán từ SePay. Vui lòng kiểm tra lại sau ít phút.');
    }
    
    /**
     * API kiểm tra trạng thái thanh toán
     */
    public function checkPaymentStatus($orderId)
    {
        try {
            $payment = Payment::where('order_id', $orderId)
                ->where('payment_method', 'sepay')
                ->first();
            
            if (!$payment) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Không tìm thấy thông tin thanh toán'
                ], 404);
            }
            
            $order = Order::find($orderId);
            
            return response()->json([
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
                'amount' => $payment->amount,
                'paid_at' => $payment->paid_at,
                'order_status' => $order ? $order->status : null,
                'message' => $payment->status === 'completed' 
                    ? 'Thanh toán thành công' 
                    : 'Đang chờ xác nhận thanh toán'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check payment status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi kiểm tra trạng thái thanh toán'
            ], 500);
        }
    }
}
