<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);
        $items = collect($cart)->map(function ($line) {
            $p = Product::find($line['product_id']);
            $voucher = $line['voucher'] ?? null;
            $subtotal = $p ? $p->price * $line['qty'] : 0;
            if (($voucher == 'Giam15k' || $voucher == 'Giam15k') && $subtotal >= 150000) {
                $subtotal -= 15000;
            }
            return $p ? ['product' => $p, 'qty' => $line['qty'], 'voucher' => $voucher, 'subtotal' => $subtotal] : null;
        })->filter();
        $total = (int)$items->sum('subtotal');
        return view('checkout.index', compact('items', 'total'));
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:COD,MOMO'],
        ]);

        $cart = session('cart', []);
        if (empty($cart)) return back()->withErrors(['cart' => 'Giỏ hàng trống']);

        $items = collect($cart)->map(function ($line) {
            $p = Product::where('active', true)->find($line['product_id']);
            return $p ? ['product' => $p, 'qty' => $line['qty'], 'subtotal' => $p->price * $line['qty']] : null;
        })->filter();
        if ($items->isEmpty()) return back()->withErrors(['cart' => 'Giỏ hàng không hợp lệ']);

        // Validate stock
        foreach ($items as $row) {
            if ($row['qty'] < 1 || $row['qty'] > $row['product']->stock) {
                return back()->withErrors(['qty' => 'Số lượng không hợp lệ']);
            }
        }

        $status = $data['payment_method'] === 'MOMO' ? 'paid' : 'pending'; // mock momo: paid

        DB::transaction(function () use ($items, $data, $status) {
            $total = (int)$items->sum('subtotal');
            $order = Order::create(array_merge($data, [
                'user_id' => Auth::id(),
                'total_price' => $total,
                'status' => $status,
            ]));

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

        session()->forget('cart');
        return redirect()->route('home')->with('status', 'Đặt hàng thành công');
    }
}
