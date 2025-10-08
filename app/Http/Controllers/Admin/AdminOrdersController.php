<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class AdminOrdersController extends Controller
{
    public function index(Request $request)
    {
        $statusMap = [
            'pending' => 'Chờ xác nhận',
            'processing' => 'Chờ lấy hàng',
            'shipped' => 'Chờ giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
        $currentStatus = $request->query('status');

        // Counts
        $counts = [];
        foreach (array_keys($statusMap) as $st) {
            $counts[$st] = Order::where('status', $st)->count();
        }
        $counts['all'] = Order::count();

        // Query orders
        $ordersQuery = Order::query()->orderBy('id', 'asc');
        if ($currentStatus && array_key_exists($currentStatus, $statusMap)) {
            $ordersQuery->where('status', $currentStatus);
        }
        $orders = $ordersQuery->with(['order_items.product'])->paginate(15);

        return view('admin.orders.index', compact('orders', 'counts', 'statusMap', 'currentStatus'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required','in:pending,processing,shipped,delivered,cancelled'],
        ]);
        $order->update(['status' => $validated['status']]);
        return back()->with('success', 'Cập nhật trạng thái đơn #' . $order->id . ' thành công');
    }
}
