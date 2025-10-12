<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
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

        // Query orders - sort ascending by ID
        $ordersQuery = Order::query()->orderBy('id', 'asc');
        if ($currentStatus && array_key_exists($currentStatus, $statusMap)) {
            $ordersQuery->where('status', $currentStatus);
        }
        $orders = $ordersQuery->with(['user', 'order_items.product'])->paginate(15);

        // Thống kê cho dashboard (giữ lại cho tương thích)
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'total_revenue' => Order::whereIn('status', ['delivered'])->sum('total_price'),
            'pending_revenue' => Order::whereIn('status', ['pending', 'processing', 'shipped'])->sum('total_price')
        ];

        return view('admin.orders.index', compact('orders', 'counts', 'statusMap', 'currentStatus', 'stats'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,shipped,delivered,cancelled'],
        ]);
        $order->update(['status' => $validated['status']]);
        return back()->with('success', 'Cập nhật trạng thái đơn #' . $order->id . ' thành công');
    }

    public function data(Request $request)
    {
        try {
            $query = Order::with(['user', 'order_items.product.product_images']);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('order_info', function ($order) {
                    $customerName = $order->user ? $order->user->name : ($order->customer_name ?? 'Khách vãng lai');
                    $itemCount = $order->order_items->count();

                    return '<div class="d-flex flex-column">
                        <div class="fw-bold text-primary">#' . str_pad($order->id, 6, '0', STR_PAD_LEFT) . '</div>
                        <small class="text-muted">' . $customerName . '</small>
                        <small class="text-info">' . $itemCount . ' sản phẩm</small>
                    </div>';
                })
                ->addColumn('customer_info', function ($order) {
                    $customerName = $order->user ? $order->user->name : ($order->customer_name ?? 'Khách vãng lai');
                    $customerPhone = $order->customer_phone ?? '';
                    $customerEmail = $order->user ? $order->user->email : ($order->customer_email ?? '');
                    
                    return '
                    <div class="fw-semibold">' . htmlspecialchars($customerName) . '</div>
                    <div class="small text-muted">' . htmlspecialchars($customerPhone) . ($customerEmail ? ' • ' . htmlspecialchars($customerEmail) : '') . '</div>';
                })
                ->addColumn('products_info', function ($order) {
                    $html = '';
                    foreach ($order->order_items->take(2) as $index => $item) {
                        $product = $item->product;
                        $img = optional($product->product_images->first())->image_url ?? null;
                        if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://', 'https://', '/'])) {
                            $img = asset($img);
                        }
                        $img = $img ?: 'https://via.placeholder.com/50';
                        
                        $html .= '
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <img src="' . $img . '" class="rounded border" style="width:50px;height:50px;object-fit:cover;" alt="' . htmlspecialchars($product->name ?? '') . '">
                            <div class="small">
                                <div class="text-truncate" style="max-width: 180px;">' . htmlspecialchars($product->name ?? 'Sản phẩm') . '</div>
                                <div class="text-muted">Số lượng: <strong>' . $item->quantity . '</strong></div>
                            </div>
                        </div>';
                    }
                    if ($order->order_items->count() > 2) {
                        $html .= '<div class="small text-muted">+' . ($order->order_items->count() - 2) . ' sản phẩm khác</div>';
                    }
                    return $html;
                })
                ->addColumn('total_formatted', function ($order) {
                    return '<span class="fw-bold text-success">' . number_format($order->total_price, 0, ',', '.') . ' VNĐ</span>';
                })
                ->addColumn('status_badge', function ($order) {
                    $statusIcons = [
                        'pending' => 'fas fa-clock',
                        'processing' => 'fas fa-cog fa-spin',
                        'shipped' => 'fas fa-shipping-fast',
                        'delivered' => 'fas fa-check-circle',
                        'cancelled' => 'fas fa-times-circle'
                    ];

                    $icon = $statusIcons[$order->status] ?? 'fas fa-question';

                    return '<span class="badge bg-' . $order->status_class . '">
                        <i class="' . $icon . ' me-1"></i>' . $order->status_text . '
                    </span>';
                })
                ->addColumn('payment_method', function ($order) {
                    $methods = [
                        'cod' => '<span class="badge bg-warning"><i class="fas fa-money-bill me-1"></i>COD</span>',
                        'bank_transfer' => '<span class="badge bg-info"><i class="fas fa-university me-1"></i>Chuyển khoản</span>',
                        'credit_card' => '<span class="badge bg-primary"><i class="fas fa-credit-card me-1"></i>Thẻ tín dụng</span>',
                        'e_wallet' => '<span class="badge bg-success"><i class="fas fa-wallet me-1"></i>Ví điện tử</span>'
                    ];

                    return $methods[$order->payment_method] ?? '<span class="badge bg-secondary">' . ucfirst($order->payment_method) . '</span>';
                })
                ->addColumn('created_date', function ($order) {
                    return '<div class="d-flex flex-column">
                        <span>' . $order->created_at->format('d/m/Y') . '</span>
                        <small class="text-muted">' . $order->created_at->format('H:i') . '</small>
                    </div>';
                })
                ->addColumn('created_formatted', function ($order) {
                    return $order->created_at->format('d/m/Y H:i');
                })
                ->addColumn('actions', function ($order) {
                    $viewButton = '<button type="button" class="btn btn-sm btn-outline-info view-order" style="margin-right:6px" 
                        data-toggle="modal" data-target="#orderDetailModal" data-id="' . $order->id . '" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </button>';

                    $editButton = '<button type="button" class="btn btn-sm btn-outline-warning edit-order" style="margin-right:6px" 
                        data-toggle="modal" data-target="#updateStatusModal" data-id="' . $order->id . '" data-status="' . $order->status . '" title="Cập nhật trạng thái">
                        <i class="fas fa-edit"></i>
                    </button>';

                    $printButton = '<button type="button" class="btn btn-sm btn-outline-success print-order" style="margin-right:6px" 
                        data-id="' . $order->id . '" title="In đơn hàng">
                        <i class="fas fa-print"></i>
                    </button>';

                    $deleteButton = '';
                    if ($order->status === 'cancelled') {
                        $deleteButton = '<button type="button" class="btn btn-sm btn-outline-danger delete-order" 
                            data-toggle="modal" data-target="#deleteOrderModal" data-id="' . $order->id . '" title="Xóa đơn hàng">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }

                    return $viewButton . ' ' . $editButton . ' ' . $printButton . ' ' . $deleteButton;
                })
                ->rawColumns(['order_info', 'customer_info', 'products_info', 'total_formatted', 'status_badge', 'payment_method', 'created_date', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('OrderController data error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $order = Order::with(['user', 'order_items.product.product_images'])->findOrFail($id);

            // Tạo timeline trạng thái
            $timeline = $this->generateOrderTimeline($order);

            // Nếu là AJAX request, trả về partial view
            if ($request->ajax()) {
                return view('admin.orders.partials.order-detail', compact('order', 'timeline'))->render();
            }

            return view('admin.orders.show', compact('order', 'timeline'));
        } catch (\Exception $e) {
            Log::error('OrderController show error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Không tìm thấy đơn hàng!'
                ], 404);
            }

            return redirect()->route('admin.orders.index')->with('error', 'Không tìm thấy đơn hàng!');
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:orders,id',
                'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
                'note' => 'nullable|string|max:500'
            ]);

            $order = Order::findOrFail($request->id);
            $oldStatus = $order->status;

            // Kiểm tra logic chuyển trạng thái
            if (!$this->canUpdateStatus($oldStatus, $request->status)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể chuyển từ trạng thái "' . $order->status_text . '" sang "' . $this->getStatusText($request->status) . '"!'
                ], 400);
            }

            $order->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

            // Log status change (có thể mở rộng thành bảng order_status_history)
            Log::info('Order status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'note' => $request->note,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái đơn hàng thành công!',
                'new_status' => $order->status_text,
                'new_status_class' => $order->status_class
            ]);
        } catch (\Exception $e) {
            Log::error('OrderController updateStatus error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $order = Order::findOrFail($request->id);

            // Chỉ cho phép xóa đơn hàng đã hủy
            if ($order->status !== 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể xóa đơn hàng đã hủy!'
                ], 400);
            }

            $order->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa đơn hàng thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('OrderController destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa đơn hàng!'
            ], 500);
        }
    }

    public function print($id)
    {
        try {
            $order = Order::with(['user', 'order_items.product.product_images'])->findOrFail($id);

            return view('admin.orders.print', compact('order'));
        } catch (\Exception $e) {
            Log::error('OrderController print error: ' . $e->getMessage());
            return redirect()->route('admin.orders.index')->with('error', 'Không tìm thấy đơn hàng!');
        }
    }

    private function canUpdateStatus($currentStatus, $newStatus)
    {
        // Logic chuyển trạng thái hợp lệ
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => [], // Không thể chuyển từ delivered
            'cancelled' => [] // Không thể chuyển từ cancelled
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    private function getStatusText($status)
    {
        $map = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã giao cho vận chuyển',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
        return $map[$status] ?? ucfirst($status);
    }

    private function generateOrderTimeline($order)
    {
        $timeline = [];
        $currentStatus = $order->status;

        // Các trạng thái theo thứ tự
        $statuses = [
            'pending' => [
                'title' => 'Đơn hàng được tạo',
                'description' => 'Đơn hàng đã được tạo và đang chờ xử lý',
                'icon' => 'fas fa-shopping-cart',
                'color' => 'secondary'
            ],
            'processing' => [
                'title' => 'Đang xử lý',
                'description' => 'Đơn hàng đang được chuẩn bị và đóng gói',
                'icon' => 'fas fa-cog',
                'color' => 'warning'
            ],
            'shipped' => [
                'title' => 'Đã giao cho vận chuyển',
                'description' => 'Đơn hàng đã được giao cho đơn vị vận chuyển',
                'icon' => 'fas fa-shipping-fast',
                'color' => 'info'
            ],
            'delivered' => [
                'title' => 'Đã giao thành công',
                'description' => 'Đơn hàng đã được giao thành công đến khách hàng',
                'icon' => 'fas fa-check-circle',
                'color' => 'success'
            ],
            'cancelled' => [
                'title' => 'Đã hủy',
                'description' => 'Đơn hàng đã bị hủy',
                'icon' => 'fas fa-times-circle',
                'color' => 'danger'
            ]
        ];

        // Xác định trạng thái hiện tại và các trạng thái đã hoàn thành
        $statusOrder = ['pending', 'processing', 'shipped', 'delivered'];
        $currentIndex = array_search($currentStatus, $statusOrder);

        if ($currentStatus === 'cancelled') {
            // Nếu đơn hàng bị hủy, chỉ hiển thị pending và cancelled
            $timeline[] = array_merge($statuses['pending'], [
                'completed' => true,
                'date' => $order->created_at->format('d/m/Y H:i')
            ]);
            $timeline[] = array_merge($statuses['cancelled'], [
                'completed' => true,
                'current' => true,
                'date' => $order->updated_at->format('d/m/Y H:i')
            ]);
        } else {
            // Timeline bình thường
            foreach ($statusOrder as $index => $status) {
                $isCompleted = $index <= $currentIndex;
                $isCurrent = $index === $currentIndex;

                $timelineItem = array_merge($statuses[$status], [
                    'completed' => $isCompleted,
                    'current' => $isCurrent
                ]);

                if ($isCompleted) {
                    $timelineItem['date'] = $index === 0 ?
                        $order->created_at->format('d/m/Y H:i') :
                        $order->updated_at->format('d/m/Y H:i');
                }

                $timeline[] = $timelineItem;
            }
        }

        return $timeline;
    }

    // API endpoint để lấy thống kê mới cho orders
    public function getStats()
    {
        try {
            $statusMap = [
                'pending' => 'Chờ xác nhận',
                'processing' => 'Chờ lấy hàng',
                'shipped' => 'Chờ giao hàng',
                'delivered' => 'Đã giao',
                'cancelled' => 'Đã hủy',
            ];

            $stats = [];
            foreach (array_keys($statusMap) as $st) {
                $stats[$st] = Order::where('status', $st)->count() ?? 0;
            }
            $stats['all'] = Order::count() ?? 0;

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy thống kê!'
            ], 500);
        }
    }
}
