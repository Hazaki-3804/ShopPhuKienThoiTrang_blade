<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Str;

class VnpayController extends Controller
{
    public function createPayment(Request $request)
    {
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_Returnurl = config('services.vnpay.return_url');

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
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);
        $query = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
        $vnp_SecureHash = hash_hmac('sha512', $query, $vnp_HashSecret);
        $vnp_Url = $vnp_Url . "?" . $query . "&vnp_SecureHash=" . $vnp_SecureHash;

        return redirect($vnp_Url);
    }
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_SecureHash = $request->get('vnp_SecureHash');
        $inputData = $request->except(['vnp_SecureHash', 'vnp_SecureHashType']);
        ksort($inputData);
        $hashData = http_build_query($inputData, '', '&', PHP_QUERY_RFC3986);
        $checkHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($checkHash === $vnp_SecureHash && $request->get('vnp_ResponseCode') == '00') {
            Payment::create([
                'order_id' => Str::before($request->get('vnp_TxnRef'), '_'),
                'amount' => $request->get('vnp_Amount') / 100,
                'status' => 'success',
                'payment_method' => 'vnpay',
                'transaction_code' => $request->get('vnp_TransactionNo'),
                'paid_at' => $this->changeDatetime($request->get('vnp_PayDate')),
            ]);
            return redirect()->route('checkout.success')->with('success', 'Thanh toán thành công!');
        } else {
            return redirect()->route('checkout.failed')->with('error', 'Thanh toán thất bại hoặc bị hủy!');
        }
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
