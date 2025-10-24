<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\MoMoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    protected $momoService;

    public function __construct(MoMoService $momoService)
    {
        $this->momoService = $momoService;
    }

    /**
     * Tạo payment request với MoMo
     */
    public function createPayment(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $total = $request->input('total');

            if (!$orderId || !$total) {
                return redirect()->route('checkout.payment')
                    ->withErrors(['payment' => 'Thông tin đơn hàng không hợp lệ']);
            }

            // Tạo payment request với MoMo
            $orderInfo = "Thanh toán đơn hàng #" . $orderId . " tại Nàng Thơ Shop";
            $result = $this->momoService->createPayment($orderId, $total, $orderInfo);

            Log::info('MoMo Payment Request', [
                'order_id' => $orderId,
                'amount' => $total,
                'result' => $result
            ]);

            // Kiểm tra response từ MoMo
            if (isset($result['resultCode']) && $result['resultCode'] == 0) {
                // Redirect đến trang thanh toán MoMo
                return redirect($result['payUrl']);
            } else {
                $errorMessage = $result['message'] ?? 'Không thể tạo yêu cầu thanh toán MoMo';
                Log::error('MoMo Payment Error', [
                    'order_id' => $orderId,
                    'error' => $errorMessage,
                    'result' => $result
                ]);

                return redirect()->route('checkout.payment')
                    ->withErrors(['payment' => $errorMessage]);
            }
        } catch (\Exception $e) {
            Log::error('MoMo Payment Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('checkout.payment')
                ->withErrors(['payment' => 'Có lỗi xảy ra khi tạo thanh toán MoMo: ' . $e->getMessage()]);
        }
    }

    /**
     * Xử lý callback từ MoMo khi user hoàn tất thanh toán
     */
    public function returnPayment(Request $request)
    {
        try {
            Log::info('MoMo Return Callback', $request->all());

            $orderId = $request->input('orderId');
            $resultCode = $request->input('resultCode');
            $message = $request->input('message');

            // Verify signature
            if (!$this->momoService->verifySignature($request->all())) {
                Log::error('MoMo Invalid Signature', $request->all());
                return redirect()->route('user.orders.show', ['order' => $orderId])
                    ->withErrors(['payment' => 'Chữ ký không hợp lệ']);
            }

            // Kiểm tra kết quả thanh toán
            if ($resultCode == 0) {
                // Thanh toán thành công
                $order = Order::find($orderId);
                
                if ($order) {
                    // Lưu thông tin payment
                    Payment::create([
                        'order_id' => $orderId,
                        'amount' => $request->input('amount'),
                        'status' => 'completed',
                        'payment_method' => 'momo',
                        'transaction_code' => $request->input('transId'),
                        'paid_at' => now(),
                    ]);

                    // Xóa giỏ hàng sau khi thanh toán thành công
                    if (Auth::check()) {
                        $cart = Cart::where('user_id', Auth::id())->first();
                        if ($cart) {
                            CartItem::where('cart_id', $cart->id)->delete();
                            $cart->delete();
                        }
                    }

                    return redirect()->route('user.orders.show', ['order' => $orderId])
                        ->with('success', 'Thanh toán qua MoMo thành công! Cảm ơn bạn đã mua hàng tại Nàng Thơ Shop!');
                }
            }

            // Thanh toán thất bại hoặc bị hủy
            return redirect()->route('user.orders.show', ['order' => $orderId])
                ->withErrors(['payment' => 'Thanh toán qua MoMo thất bại: ' . $message]);

        } catch (\Exception $e) {
            Log::error('MoMo Return Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $orderId = $request->input('orderId');
            return redirect()->route('user.orders.show', ['order' => $orderId])
                ->withErrors(['payment' => 'Có lỗi xảy ra khi xử lý thanh toán MoMo']);
        }
    }

    /**
     * Xử lý IPN (Instant Payment Notification) từ MoMo
     * Đây là webhook mà MoMo gọi để thông báo kết quả thanh toán
     */
    public function notifyPayment(Request $request)
    {
        try {
            Log::info('MoMo IPN Notification', $request->all());

            // Verify signature
            if (!$this->momoService->verifySignature($request->all())) {
                Log::error('MoMo IPN Invalid Signature', $request->all());
                return response()->json([
                    'resultCode' => 97,
                    'message' => 'Invalid signature'
                ]);
            }

            $orderId = $request->input('orderId');
            $resultCode = $request->input('resultCode');

            if ($resultCode == 0) {
                // Thanh toán thành công
                $order = Order::find($orderId);
                
                if ($order) {
                    // Kiểm tra xem payment đã tồn tại chưa
                    $existingPayment = Payment::where('order_id', $orderId)
                        ->where('payment_method', 'momo')
                        ->first();

                    if (!$existingPayment) {
                        Payment::create([
                            'order_id' => $orderId,
                            'amount' => $request->input('amount'),
                            'status' => 'completed',
                            'payment_method' => 'momo',
                            'transaction_code' => $request->input('transId'),
                            'paid_at' => now(),
                        ]);
                    }

                    Log::info('MoMo Payment Completed', [
                        'order_id' => $orderId,
                        'transaction_id' => $request->input('transId')
                    ]);
                }
            }

            // Trả về response cho MoMo
            return response()->json([
                'resultCode' => 0,
                'message' => 'Success'
            ]);

        } catch (\Exception $e) {
            Log::error('MoMo IPN Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'resultCode' => 99,
                'message' => 'System error'
            ]);
        }
    }
}
