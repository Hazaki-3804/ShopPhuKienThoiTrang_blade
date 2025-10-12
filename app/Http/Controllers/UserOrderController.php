<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class UserOrderController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $status = $request->query('status'); // optional filter

        // Count orders per status for current user
        $statusMap = [
            'pending' => 'Chờ xác nhận',
            'processing' => 'Chờ lấy hàng',
            'shipped' => 'Chờ giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];

        $counts = [];
        foreach (array_keys($statusMap) as $st) {
            $counts[$st] = Order::where('user_id', $userId)->where('status', $st)->count();
        }
        $counts['all'] = Order::where('user_id', $userId)->count();

        // Simple delivered count can act as "Đánh giá" pending bucket
        $reviewableCount = $counts['delivered'] ?? 0;

        // Fetch recent orders (optionally filtered)
        $ordersQuery = Order::where('user_id', $userId)->latest();
        if ($status && in_array($status, array_keys($statusMap))) {
            $ordersQuery->where('status', $status);
        }
        $orders = $ordersQuery->with(['order_items.product.product_images'])->paginate(10);

        return view('user_orders.index', [
            'orders' => $orders,
            'counts' => $counts,
            'statusMap' => $statusMap,
            'currentStatus' => $status,
            'reviewableCount' => $reviewableCount,
        ]);
    }

    public function show(Order $order)
    {
        $this->authorizeOrder($order);
        $order->load(['order_items.product.product_images']);
        $statusMap = [
            'pending' => 'Chờ xác nhận',
            'processing' => 'Chờ lấy hàng',
            'shipped' => 'Chờ giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
        $canCancel = in_array($order->status, ['pending','processing']);
        return view('user_orders.show', compact('order','statusMap','canCancel'));
    }

    public function cancel(Order $order)
    {
        $this->authorizeOrder($order);
        if (!in_array($order->status, ['pending','processing'])) {
            return back()->with('status', 'Đơn không thể hủy ở trạng thái hiện tại');
        }
        $order->update(['status' => 'cancelled']);
        return redirect()->route('user.orders.show', $order)->with('success', 'Đã hủy đơn hàng thành công.');
    }

    private function authorizeOrder(Order $order): void
    {
        if (auth()->id() !== $order->user_id) {
            abort(403);
        }
    }
}
