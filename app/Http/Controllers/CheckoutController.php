<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\MoMoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private function currentCart(): ?Cart
    {
        if (!Auth::check()) return null;
        $userId = Auth::id();
        return Cart::firstOrCreate(['user_id' => $userId], ['user_id' => $userId]);
    }

    public function index(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
        $cart = $this->currentCart();
        if (!$cart) return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);

        $selected = collect((array)$request->query('selected', []))
            ->map(fn($v) => (int)$v)
            ->filter()->unique()->values();

        $rows = CartItem::where('cart_id', $cart->id)
            ->when($selected->isNotEmpty(), fn($q) => $q->whereIn('product_id', $selected->all()))
            ->with(['product.product_images'])
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('products.status', 1)
            ->select('cart_items.*')
            ->get();

        $items = $rows->map(function ($ci) {
            $p = $ci->product;
            if (!$p) return null;
            $price = (int) $p->price;
            $qty = (int) $ci->quantity;
            return [
                'product' => $p,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->filter();

        if ($items->isEmpty()) {
            // Nếu có truyền selected mà rỗng/không hợp lệ thì quay lại giỏ
            if ($selected->isNotEmpty()) {
                return redirect()->route('cart.index')->withErrors(['cart' => 'Vui lòng chọn sản phẩm hợp lệ để thanh toán']);
            }
            return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);
        }

        $total = (int) $items->sum('subtotal');
        return view('checkout.index', [
            'items' => $items,
            'total' => $total,
            'selected' => $selected->all(),
        ]);
    }

    public function saveAddress(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required','string','max:120'],
            'customer_email' => ['required','email'],
            'customer_phone' => ['required','string','max:30'],
            'customer_address' => ['required','string','max:255'],
        ]);

        // Lưu thông tin vào session
        session([
            'checkout_address' => $data,
            'checkout_selected' => $request->input('selected', [])
        ]);

        return redirect()->route('checkout.payment');
    }

    public function payment()
    {
        if (!Auth::check()) return redirect()->route('login');
        
        $addressData = session('checkout_address');
        if (!$addressData) {
            return redirect()->route('checkout.index')->withErrors(['address' => 'Vui lòng nhập địa chỉ giao hàng']);
        }

        $cart = $this->currentCart();
        if (!$cart) return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);

        $selected = collect((array)session('checkout_selected', []))
            ->map(fn($v) => (int)$v)
            ->filter()->unique()->values();

        $rows = CartItem::where('cart_id', $cart->id)
            ->when($selected->isNotEmpty(), fn($q) => $q->whereIn('product_id', $selected->all()))
            ->with(['product.product_images'])
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('products.status', 1)
            ->select('cart_items.*')
            ->get();

        $items = $rows->map(function ($ci) {
            $p = $ci->product;
            if (!$p) return null;
            $price = (int) $p->price;
            $qty = (int) $ci->quantity;
            return [
                'product' => $p,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->filter();

        if ($items->isEmpty()) {
            return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);
        }

        $subtotal = (int) $items->sum('subtotal');
        $shippingFee = 30000; // Phí vận chuyển 30.000đ
        
        // Kiểm tra voucher
        $availableVouchers = [];
        if ($subtotal >= 250000) {
            $availableVouchers[] = [
                'code' => 'GIAM15K',
                'label' => 'Giảm 15k cho đơn từ 250k',
                'discount' => 15000,
                'min_order' => 250000
            ];
        }
        
        $total = $subtotal + $shippingFee;

        return view('checkout.payment', [
            'items' => $items,
            'addressData' => $addressData,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'availableVouchers' => $availableVouchers,
        ]);
    }

    public function place(Request $request)
    {
        $paymentMethod = $request->input('payment_method', 'cod');
        $voucherCode = $request->input('voucher_code');
        $requestInvoice = $request->input('request_invoice', false);
        
        $addressData = session('checkout_address');
        if (!$addressData) {
            return redirect()->route('checkout.index')->withErrors(['address' => 'Vui lòng nhập địa chỉ giao hàng']);
        }

        $cart = $this->currentCart();
        if (!$cart) return back()->withErrors(['cart' => 'Giỏ hàng trống']);

        $selected = collect((array)session('checkout_selected', []))
            ->map(fn($v) => (int)$v)
            ->filter()->unique()->values();

        $rows = CartItem::where('cart_id', $cart->id)
            ->when($selected->isNotEmpty(), fn($q) => $q->whereIn('product_id', $selected->all()))
            ->with(['product'])
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('products.status', 1)
            ->select('cart_items.*')
            ->get();

        $items = $rows->map(function ($ci) {
            $p = $ci->product;
            if (!$p) return null;
            $price = (int) $p->price;
            $qty = (int) $ci->quantity;
            return [
                'product' => $p,
                'qty' => $qty,
                'subtotal' => $price * $qty,
            ];
        })->filter();
        if ($items->isEmpty()) return back()->withErrors(['cart' => 'Giỏ hàng không hợp lệ hoặc chưa chọn sản phẩm']);

        // Validate stock
        foreach ($items as $row) {
            if ($row['qty'] < 1 || $row['qty'] > $row['product']->stock) {
                return back()->withErrors(['qty' => 'Số lượng không hợp lệ']);
            }
        }

        $subtotal = (int)$items->sum('subtotal');
        $discount = 0;
        $shippingFee = 30000; // same as payment() step
        
        // Áp dụng voucher
        if ($voucherCode === 'GIAM15K' && $subtotal >= 250000) {
            $discount = 15000;
        }
        
        $total = $subtotal + $shippingFee - $discount;
        $order = null;

        DB::transaction(function() use ($items, $addressData, $paymentMethod, $total, &$order) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $addressData['customer_name'],
                'customer_email' => $addressData['customer_email'],
                'customer_phone' => $addressData['customer_phone'],
                'shipping_address' => $addressData['customer_address'],
                'total_price' => $total,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
            ]);

            foreach ($items as $row) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $row['product']->id,
                    'quantity' => $row['qty'],
                    'price' => $row['product']->price,
                ]);
                // reduce stock
                $row['product']->decrement('stock', $row['qty']);
            }
        });

        // Nếu thanh toán MoMo, chuyển hướng đến MoMo
        if ($paymentMethod === 'momo') {
            $momoService = new MoMoService();
            $orderInfo = "Thanh toán đơn hàng #" . $order->id;
            $result = $momoService->createPayment($order->id, $total, $orderInfo);

            if (isset($result['payUrl'])) {
                // Lưu order ID vào session để xử lý callback
                session(['momo_order_id' => $order->id]);
                return redirect($result['payUrl']);
            } else {
                return back()->withErrors(['payment' => 'Không thể kết nối đến MoMo. Vui lòng thử lại.']);
            }
        }

        // Xóa session và cart items cho COD
        session()->forget(['checkout_address', 'checkout_selected']);
        if ($cart) {
            CartItem::where('cart_id', $cart->id)->delete();
        }

        // Nếu yêu cầu hóa đơn, chuyển đến trang in hóa đơn
        if ($requestInvoice) {
            return redirect()->route('invoice.show', $order->id);
        }

        return redirect()->route('home')->with('status', 'Đặt hàng thành công');
    }

    public function momoReturn(Request $request)
    {
        $orderId = $request->input('orderId');
        $resultCode = $request->input('resultCode');

        if ($resultCode == 0) {
            // Thanh toán thành công
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => 'confirmed']);
            }

            // Xóa session và cart items
            session()->forget(['checkout_address', 'checkout_selected', 'momo_order_id']);
            $cart = $this->currentCart();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->delete();
            }

            return redirect()->route('home')->with('status', 'Thanh toán MoMo thành công! Đơn hàng #' . $orderId);
        } else {
            // Thanh toán thất bại
            return redirect()->route('checkout.payment')->withErrors(['payment' => 'Thanh toán MoMo thất bại. Vui lòng thử lại.']);
        }
    }

    public function momoNotify(Request $request)
    {
        $momoService = new MoMoService();
        $data = $request->all();

        if ($momoService->verifySignature($data)) {
            $orderId = $data['orderId'];
            $resultCode = $data['resultCode'];

            if ($resultCode == 0) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update(['status' => 'confirmed']);
                }
            }

            return response()->json(['message' => 'OK'], 200);
        }

        return response()->json(['message' => 'Invalid signature'], 400);
    }
}
