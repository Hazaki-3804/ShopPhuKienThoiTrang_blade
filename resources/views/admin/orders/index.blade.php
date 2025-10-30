@extends('layouts.admin')
@section('title', 'Danh sách đơn hàng')

@section('content_header')
<h1></h1>
@stop

@section('content')
@php $map = $statusMap ?? []; @endphp
<div class="shadow-sm rounded bg-white py-2">
    <div class="col-12 col-sm-6 col-lg">
        <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <h4 class="fw-semibold m-0">Quản lý đơn hàng</h4>
            <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý đơn hàng']]" />
        </div>
    </div>
    <!-- Stats Cards -->
    <div class="row mb-4 px-4">
        <div class="col-12 col-sm-6 col-lg mb-1">
            <div class="stats-card stats-pending">
                <div class="stats-content">
                    <div class="stats-number">{{ $counts['pending'] ?? 0 }}</div>
                    <div class="stats-label">Chờ xác nhận</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-clipboard-check"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="stats-card stats-processing">
                <div class="stats-content">
                    <div class="stats-number">{{ $counts['processing'] ?? 0 }}</div>
                    <div class="stats-label">Chờ lấy hàng</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="stats-card stats-shipped">
                <div class="stats-content">
                    <div class="stats-number">{{ $counts['shipped'] ?? 0 }}</div>
                    <div class="stats-label">Chờ giao hàng</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-truck"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="stats-card stats-delivered">
                <div class="stats-content">
                    <div class="stats-number">{{ $counts['delivered'] ?? 0 }}</div>
                    <div class="stats-label">Đã giao</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg">
            <div class="stats-card stats-cancelled">
                <div class="stats-content">
                    <div class="stats-number">{{ $counts['cancelled'] ?? 0 }}</div>
                    <div class="stats-label">Đã hủy</div>
                </div>
                <div class="stats-icon">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center pl-4">
                <ul class="nav nav-pills-custom gap-2 flex-wrap">
                    <li class="nav-item"><a class="nav-link {{ empty($currentStatus) ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">Tất cả ({{ $counts['all'] ?? 0 }})</a></li>
                    @foreach($map as $key => $label)
                    <li class="nav-item"><a class="nav-link pill-{{ $key }} {{ ($currentStatus === $key) ? 'active' : '' }}" href="{{ route('admin.orders.index', ['status' => $key]) }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body table-responsive">
                <div class="d-flex justify-content-between align-items-center mb-2 pe-2">
                    <span class="fw-bold mb-3">Danh sách đơn hàng</span>
                    <form method="GET" class="d-flex" style="gap: 5px;">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Tìm theo mã, email, SĐT..." style="width: 260px;">
                        @if($currentStatus)
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                        @endif
                        <button class="btn btn-outline-secondary btn-sm">Tìm</button>
                    </form>
                </div>
                <table class="table table-bordered table-striped align-middle w-100" id="ordersTable">
                    <thead class="table-info">
                        <tr>
                            <th>STT</th>
                            <th>Khách hàng</th>
                            <th style="min-width: 250px;">Sản phẩm</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                            <th>Ngày tạo</th>
                            @canany(['change status orders', 'print orders', 'view order detail'])
                            <th class="text-end">Thao tác</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="fw-semibold">{{ $order->customer_name }}</div>
                                    <div class="small text-muted">{{ $order->customer_phone }} • {{ $order->customer_email }}</div>
                                </div>
                            </td>
                            <td>
                                @foreach($order->order_items as $index => $item)
                                @if($index < 2)
                                    @php
                                    $product=$item->product;
                                    $img = optional($product->product_images->first())->image_url ?? null;
                                    if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) {
                                    $img = asset($img);
                                    }
                                    $img = $img ?: 'https://via.placeholder.com/50';
                                    @endphp
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <img src="{{ $img }}" class="rounded border" style="width:50px;height:50px;object-fit:cover;" alt="{{ $product->name ?? '' }}">
                                        <div class="small">
                                            <div class="text-truncate" style="max-width: 180px;">{{ $product->name ?? 'Sản phẩm' }}</div>
                                            <div class="text-muted">Số lượng: <strong>{{ $item->quantity }}</strong></div>
                                        </div>
                                    </div>
                                    @endif
                                    @endforeach
                                    @if($order->order_items->count() > 2)
                                    <div class="small text-muted">+{{ $order->order_items->count() - 2 }} sản phẩm khác</div>
                                    @endif
                            </td>
                            <td>
                                @php
                                $statusColors = [
                                'pending' => 'warning',
                                'processing' => 'info',
                                'shipped' => 'primary',
                                'delivered' => 'success',
                                'cancelled' => 'danger'
                                ];
                                $color = $statusColors[$order->status] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $color }}">{{ $map[$order->status] ?? $order->status }}</span>
                            </td>
                            <td>{{ number_format($order->total_price,0,',','.') }}₫</td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            @canany(['change status orders', 'print orders', 'view order detail'])
                            <td class="text-end">
                                <div class="dropdown text-center">
                                    <button class="btn btn-sm btn-light border-0" type="button" id="actionsMenu{{ $order->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="box-shadow:none;">
                                        <i class="fas fa-ellipsis-v text-secondary"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 rounded" aria-labelledby="actionsMenu{{ $order->id }}">

                                        @if(auth()->user()->can('view order detail'))
                                        <a class="dropdown-item view-order-detail" href="#" data-order-id="{{ $order->id }}">
                                            <i class="bi bi-eye text-info mr-2"></i>Xem chi tiết
                                        </a>
                                        @endif

                                        @if(auth()->user()->can('change status orders'))
                                        @if(!in_array($order->status, ['delivered', 'cancelled']))
                                        <a class="dropdown-item edit-status-btn" href="#" data-order-id="{{ $order->id }}" data-current-status="{{ $order->status }}">
                                            <i class="bi bi-toggle-on text-warning mr-2"></i>Chỉnh sửa trạng thái
                                        </a>
                                        @endif
                                        @endif

                                        @if(auth()->user()->can('print orders'))
                                        @if (Route::has('invoice.show'))
                                        <a class="dropdown-item" href="{{ route('invoice.show', $order->id) }}" target="_blank">
                                            <i class="bi bi-printer text-secondary mr-2"></i>In hóa đơn
                                        </a>
                                        @endif
                                        @endif

                                    </div>
                                </div>
                            </td>
                            @endcanany
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Không có đơn hàng</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-2">
                    {{ $orders->withQueryString()->links() }}
                </div>
            </div>
        </div>

        <!-- Modal Chỉnh sửa trạng thái -->
        <div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title font-weight-bold" id="editStatusModalLabel"><i class="fas fa-edit mr-2"></i>Chỉnh sửa trạng thái đơn hàng</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form id="editStatusForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Trạng thái hiện tại:</label>
                                <p id="currentStatusText" class="mb-3"></p>
                            </div>
                            <div class="form-group">
                                <label for="newStatus" class="font-weight-bold">Chọn trạng thái mới: <span class="text-danger">*</span></label>
                                <select name="status" id="newStatus" class="form-control" required>
                                    <option value="">-- Chọn trạng thái --</option>
                                </select>
                                <small class="form-text text-muted">Bạn chỉ có thể chuyển sang trạng thái tiếp theo trong quy trình.</small>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Hủy</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i>Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Chi tiết đơn hàng -->
        <div class="modal fade" id="orderDetailModal" tabindex="-1" role="dialog" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title font-weight-bold" id="orderDetailModalLabel"><i class="fas fa-file-invoice mr-2"></i>Chi tiết đơn hàng</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="orderDetailContent">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Đang tải...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
    /* Modern Stats Cards */
    .stats-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 100px;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .stats-content {
        flex: 1;
    }

    .stats-number {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 4px;
        color: #fff;
    }

    .stats-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stats-icon {
        width: 56px;
        height: 56px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: rgba(255, 255, 255, 0.9);
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
    }

    /* Color variants */
    .stats-pending {
        background: linear-gradient(135deg, #FFA726 0%, #FF9800 100%);
    }

    .stats-processing {
        background: linear-gradient(135deg, #42A5F5 0%, #2196F3 100%);
    }

    .stats-shipped {
        background: linear-gradient(135deg, #26C6DA 0%, #00BCD4 100%);
    }

    .stats-delivered {
        background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
    }

    .stats-cancelled {
        background: linear-gradient(135deg, #EF5350 0%, #F44336 100%);
    }

    /* Enhanced nav pills */
    .nav-pills-custom .nav-link {
        background: #f8f9fa;
        color: #6c757d;
        border-radius: 10px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .nav-pills-custom .nav-link:hover {
        background: #e9ecef;
        transform: translateY(-1px);
    }

    .nav-pills-custom .nav-link.active {
        background: linear-gradient(135deg, #ff6b35 0%, #EE4D2D 100%);
        color: #ffffff;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(238, 77, 45, 0.3);
    }

    /* Colored pills per status matching stats cards */
    .nav-pills-custom .pill-pending.active {
        background: linear-gradient(135deg, #FFA726 0%, #FF9800 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(255, 167, 38, 0.3);
    }

    .nav-pills-custom .pill-processing.active {
        background: linear-gradient(135deg, #42A5F5 0%, #2196F3 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(66, 165, 245, 0.3);
    }

    .nav-pills-custom .pill-shipped.active {
        background: linear-gradient(135deg, #26C6DA 0%, #00BCD4 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(38, 198, 218, 0.3);
    }

    .nav-pills-custom .pill-delivered.active {
        background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(102, 187, 106, 0.3);
    }

    .nav-pills-custom .pill-cancelled.active {
        background: linear-gradient(135deg, #EF5350 0%, #F44336 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(239, 83, 80, 0.3);
    }

    /* Fix dropdown trong table */
    .table-responsive {
        overflow: visible !important;
    }

    table tbody tr td {
        overflow: visible !important;
    }

    /* Đảm bảo dropdown hiển thị đúng */
    .dropdown-menu {
        z-index: 1050 !important;
    }
</style>
@endpush
@push('scripts')
<script>
    $(document).ready(function() {
        // Dropdown sẽ tự động hoạt động với Bootstrap 4.6.1 đã được AdminLTE import

        // Định nghĩa mapping trạng thái
        const statusMap = {
            'pending': 'Chờ xác nhận',
            'processing': 'Chờ lấy hàng',
            'shipped': 'Chờ giao hàng',
            'delivered': 'Đã giao',
            'cancelled': 'Đã hủy'
        };

        // Định nghĩa quy tắc chuyển trạng thái
        const statusFlow = {
            'pending': ['processing', 'shipped', 'delivered', 'cancelled'],
            'processing': ['shipped', 'delivered', 'cancelled'],
            'shipped': ['delivered', 'cancelled']
        };

        // Xử lý click vào nút chỉnh sửa trạng thái
        $(document).on('click', '.edit-status-btn', function(e) {
            e.preventDefault();
            const orderId = $(this).data('order-id');
            const currentStatus = $(this).data('current-status');

            // Hiển thị trạng thái hiện tại
            $('#currentStatusText').html('<span class="badge badge-info">' + statusMap[currentStatus] + '</span>');

            // Lấy danh sách trạng thái có thể chuyển
            const availableStatuses = statusFlow[currentStatus] || [];

            // Xóa các option cũ và thêm option mới
            const selectElement = $('#newStatus');
            selectElement.empty();
            selectElement.append('<option value="">-- Chọn trạng thái --</option>');

            availableStatuses.forEach(function(status) {
                selectElement.append('<option value="' + status + '">' + statusMap[status] + '</option>');
            });

            // Set action cho form - sử dụng route helper
            $('#editStatusForm').attr('action', '{{ url("admin/orders") }}/' + orderId + '/update-status');

            // Hiển thị modal - Sử dụng Bootstrap 4.6.1
            $('#editStatusModal').modal('show');
        });

        // Xử lý submit form chỉnh sửa trạng thái
        $('#editStatusForm').on('submit', function(e) {
            e.preventDefault();

            const form = $(this);
            const url = form.attr('action');
            const formData = new FormData(this);

            // Disable submit button
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Đang cập nhật...');

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Đóng modal - Sử dụng Bootstrap 4.6.1
                        $('#editStatusModal').modal('hide');

                        // Hiển thị thông báo thành công
                        if (typeof AjaxFormHandler !== 'undefined') {
                            AjaxFormHandler.showToast('Cập nhật trạng thái đơn hàng thành công!', 'success');
                        }

                        // Reload trang
                        window.location.reload();
                    } else {
                        if (typeof AjaxFormHandler !== 'undefined') {
                            AjaxFormHandler.showToast(data.message || 'Có lỗi xảy ra, vui lòng thử lại', 'danger');
                        }
                        submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Cập nhật');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra, vui lòng thử lại');
                    submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Cập nhật');
                });
        });

        // Xử lý click vào nút xem chi tiết
        $(document).on('click', '.view-order-detail', function(e) {
            e.preventDefault();
            const orderId = $(this).data('order-id');
            const modalContent = document.getElementById('orderDetailContent');

            // Hiển thị loading
            modalContent.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Đang tải...</span>
                    </div>
                </div>
            `;

            // Hiển thị modal - Sử dụng Bootstrap 4.6.1
            $('#orderDetailModal').modal('show');

            // Gọi API để lấy chi tiết đơn hàng
            fetch('/admin/orders/' + orderId + '/detail')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        modalContent.innerHTML = data.html;
                    } else {
                        modalContent.innerHTML = `
                            <div class="alert alert-danger">
                                ${data.message || 'Không thể tải thông tin đơn hàng'}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    modalContent.innerHTML = `
                        <div class="alert alert-danger">
                            Có lỗi xảy ra khi tải thông tin đơn hàng
                        </div>
                    `;
                });
        });
    });
</script>
@endpush