<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Str;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class VnpayController extends Controller
{
    public function createPayment(Request $request)
    {
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_ReturnUrl = config('services.vnpay.return_url');

        // Kiểm tra config VNPAY
        if (empty($vnp_TmnCode) || empty($vnp_HashSecret) || empty($vnp_ReturnUrl)) {
            Log::error('VNPAY config is missing', [
                'tmn_code' => $vnp_TmnCode ? 'OK' : 'MISSING',
                'hash_secret' => $vnp_HashSecret ? 'OK' : 'MISSING',
                'return_url' => $vnp_ReturnUrl ? 'OK' : 'MISSING',
            ]);
            return back()->with(['error' => 'Cấu hình thanh toán VNPAY chưa đầy đủ. Vui lòng liên hệ quản trị viên.']);
        }

        $vnp_TxnRef = $request->input('order_id').'_'.time(); // Mã đơn hàng
        $vnp_OrderInfo = "Thanh toán đơn hàng tại Nàng Thơ Shop";   
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $request->input('total') * 100; // VNĐ * 100
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        // 🔹 Bước 1: Sắp xếp key theo thứ tự alphabet
        ksort($inputData);

        // 🔹 Bước 2: Tạo query string
        $query = [];
        foreach ($inputData as $key => $value) {
            if ($value != null && $value != '') {
                $query[] = urlencode($key) . "=" . urlencode($value);
            }
        }
        $queryString = implode('&', $query);

        // 🔹 Bước 3: Ký bằng HMAC SHA512
        $vnp_SecureHash = hash_hmac('sha512', $queryString, $vnp_HashSecret);

        // 🔹 Bước 4: Tạo URL redirect
        $paymentUrl = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?" . $queryString . '&vnp_SecureHash=' . $vnp_SecureHash;
        
        Log::info('VNPAY Payment URL created', [
            'order_id' => $request->input('order_id'),
            'amount' => $vnp_Amount,
            'url' => $paymentUrl
        ]);

        return redirect($paymentUrl);
    }
    public function returnPayment(Request $request)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_SecureHash = $request->get('vnp_SecureHash');
        $inputData = $request->except(['vnp_SecureHash', 'vnp_SecureHashType']);
        ksort($inputData, SORT_STRING);

        $hashDataArr = [];
        foreach ($inputData as $key => $value) {
            $hashDataArr[] = urlencode($key) . "=" . urlencode($value);
        }
        $hashData = implode('&', $hashDataArr);
        $orderId = (int) Str::before($request->get('vnp_TxnRef'), '_');
        $checkHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        if ($checkHash === $vnp_SecureHash && $request->get('vnp_ResponseCode') == '00') {
            Payment::create([
                'order_id' => $orderId,
                'amount' => $request->get('vnp_Amount') / 100,
                'status' => 'completed',
                'payment_method' => 'vnpay',
                'transaction_code' => $request->get('vnp_TransactionNo'),
                'paid_at' => $this->changeDatetime($request->get('vnp_PayDate')),
            ]);
            // Clear current user's cart after successful payment
            if (Auth::check()) {
                $cart = Cart::where('user_id', Auth::id())->first();
                if ($cart) {
                    CartItem::where('cart_id', $cart->id)->delete();
                    $cart->delete();
                }
            }
            return redirect()->route('user.orders.show', ['order' => $orderId])
                ->with('success', 'Thanh toán qua VNPAY thành công! Cảm ơn bạn đã mua hàng của Nàng Thơ Shop!');
        }
        // Payment failed or invalid signature
        return redirect()->route('user.orders.show', ['order' => $orderId])
            ->withErrors(['payment' => 'Thanh toán qua VNPAY thất bại hoặc bị hủy!']);
    }
    private function changeDatetime($payDate){
        $datetime_sql = substr($payDate,0,4) . '-' .  // Năm
                        substr($payDate,4,2) . '-' .  // Tháng
                        substr($payDate,6,2) . ' ' .  // Ngày
                        substr($payDate,8,2) . ':' .  // Giờ
                        substr($payDate,10,2) . ':' . // Phút
                        substr($payDate,12,2);        // Giây
        return $datetime_sql;
    }
}
