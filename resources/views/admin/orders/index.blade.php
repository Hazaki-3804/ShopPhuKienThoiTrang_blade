@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Danh sách đơn hàng</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý đơn hàng']]" />
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 px-3">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-primary mb-1">{{ number_format($stats['total_orders']) }}</h5>
                    <p class="text-muted mb-0 small">Tổng đơn hàng</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3">
                            <i class="fas fa-clock fa-2x text-secondary"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-secondary mb-1">{{ number_format($stats['pending_orders']) }}</h5>
                    <p class="text-muted mb-0 small">Chờ xử lý</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-cog fa-2x text-warning"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-warning mb-1">{{ number_format($stats['processing_orders']) }}</h5>
                    <p class="text-muted mb-0 small">Đang xử lý</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-shipping-fast fa-2x text-info"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-info mb-1">{{ number_format($stats['shipped_orders']) }}</h5>
                    <p class="text-muted mb-0 small">Đang giao</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-success mb-1">{{ number_format($stats['delivered_orders']) }}</h5>
                    <p class="text-muted mb-0 small">Đã giao</p>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-danger mb-1">{{ number_format($stats['cancelled_orders']) }}</h5>
                    <p class="text-muted mb-0 small">Đã hủy</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Card con chứa table -->
    <div class="card m-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <!-- Trái -->
            <div class="flex-grow-1">
                <input type="search" id="orderSearch"
                    class="form-control form-control-sm"
                    placeholder="Tìm kiếm đơn hàng..."
                    style="max-width: 220px;">
            </div>

            <!-- Phải -->
            <div class="d-flex align-items-center">
                <div class="mr-2">
                    <select id="statusFilter" class="form-control form-control-sm">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="shipped">Đang giao</option>
                        <option value="delivered">Đã giao</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>

                <div class="mr-2">
                    <input type="date" id="dateFromFilter" class="form-control form-control-sm" placeholder="Từ ngày">
                </div>
                
                <div class="mr-2">
                    <input type="date" id="dateToFilter" class="form-control form-control-sm" placeholder="Đến ngày">
                </div>

                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-export"></i> Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                        <a class="dropdown-item" href="#" id="btn-excel"><i class="fas fa-file-excel"></i> Excel</a>
                        <a class="dropdown-item" href="#" id="btn-csv"><i class="fas fa-file-csv"></i> CSV</a>
                        <a class="dropdown-item" href="#" id="btn-pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                        <a class="dropdown-item" href="#" id="btn-print"><i class="fas fa-print"></i> Print</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive no-scrollbar">
                <table class="table table-bordered table-striped align-middle w-100" id="ordersTable">
                    <thead class="table-info">
                        <tr>
                            <th>ID</th>
                            <th>Thông tin đơn hàng</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thanh toán</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Chi tiết đơn hàng
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Đang tải...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Cập nhật trạng thái
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>           
            </div>
            <form id="updateStatusForm">
                <div class="modal-body">
                    <input type="hidden" id="updateOrderId">
                    <div class="mb-3">
                        <label class="form-label">Trạng thái mới</label>
                        <select class="form-control" id="newStatus" required>
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="pending">Chờ xử lý</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="shipped">Đang giao</option>
                            <option value="delivered">Đã giao</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="statusNote" rows="3" placeholder="Nhập ghi chú về việc thay đổi trạng thái..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Order Modal -->
<x-admin.modal
    modal_id='deleteOrderModal'
    title="Xóa đơn hàng"
    url="{{ route('admin.orders.destroy') }}"
    button_type="delete"
    method="DELETE">
    <div class="mb-3 rounded-2">
        <input type="hidden" name="id" id="del_order_id">
        <label for="delete_order" class="form-label fw-bold">Bạn có chắc chắn muốn xóa đơn hàng này? <br>Dữ liệu sẽ không thể khôi phục.</label>
        <p class="text-danger mt-2"><strong>Lưu ý:</strong> Chỉ có thể xóa đơn hàng đã hủy!</p>
    </div>
</x-admin.modal>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
<style>
.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 25px;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #dee2e6;
}

.timeline-item.completed .timeline-content {
    border-left-color: #28a745;
}

.timeline-item.current .timeline-content {
    border-left-color: #007bff;
    background: #e3f2fd;
}
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        window.table = $('#ordersTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.orders.data') }}",
                type: 'GET',
                data: function(d) {
                    d.status = $('#statusFilter').val();
                    d.date_from = $('#dateFromFilter').val();
                    d.date_to = $('#dateToFilter').val();
                }
            },
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                "t" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'excelHtml5',
                    bom: true,
                    charset: 'utf-8',
                    className: 'buttons-excel',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'csvHtml5',
                    className: 'buttons-csv',
                    bom: true,
                    charset: 'utf-8',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'buttons-pdf',
                    exportOptions: {
                        columns: ':visible:not(:last-child)'
                    }
                },
            ],
            columns: [{
                    data: 'id',
                    name: 'id',
                },
                {
                    data: 'order_info',
                    name: 'order_info',
                    orderable: false
                },
                {
                    data: 'total_formatted',
                    name: 'total_price',
                    orderable: true,
                    render: function(data) {
                        return data;
                    }
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: true,
                    searchable: false,
                    render: function(data) {
                        return data;
                    }
                },
                {
                    data: 'payment_method',
                    name: 'payment_method',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return data;
                    }
                },
                {
                    data: 'created_date',
                    name: 'created_at',
                    orderable: true,
                    render: function(data) {
                        return data;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                className: 'text-center'
            }],
            responsive: true,
            language: {
                search: 'Tìm kiếm:',
                lengthMenu: 'Hiển thị _MENU_ đơn hàng',
                info: 'Hiển thị _START_ đến _END_ trong tổng _TOTAL_ đơn hàng',
                infoEmpty: 'Không có dữ liệu',
                zeroRecords: '🔍Không tìm thấy đơn hàng',
                infoFiltered: '(lọc từ tổng _MAX_ đơn hàng)',
                loadingRecords: '<i class="fas fa-spinner fa-spin"></i> Đang tải...',
                emptyTable: 'Không có đơn hàng nào để hiển thị.',
                processing: '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...',
                paginate: {
                    previous: 'Trước',
                    next: 'Sau'
                }
            },
            drawCallback: function() {
                $('.dataTables_paginate').addClass('justify-content-end');
            },
            searching: true
        });
        
        let typingTimer;
        const typingDelay = 500; // 500ms delay

        $('#orderSearch').on('keyup', function() {
            clearTimeout(typingTimer);
            const value = this.value;

            typingTimer = setTimeout(function() {
                table.search(value).draw();
            }, typingDelay);
        });
    
        $('#statusFilter, #dateFromFilter, #dateToFilter').on('change', function() {
            table.ajax.reload();
        });
        
        // Gán click dropdown -> gọi DataTables button
        $('#btn-excel').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-excel').trigger();
        });
        $('#btn-csv').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-csv').trigger();
        });
        $('#btn-pdf').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-pdf').trigger();
        });
        $('#btn-print').on('click', function(e) {
            e.preventDefault();
            table.button('.buttons-print').trigger();
        });
    
        // Print order
        $(document).on('click', '.print-order', function() {
            const orderId = $(this).data('id');
            window.open(`{{ route('admin.orders.index') }}/${orderId}/print`, '_blank');
        });
    
        $(document).on('click', '.view-order', function() {
            let orderId = $(this).data('id');
            $('#orderDetailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Đang tải...</p></div>');
            
            // Load order data
            $.ajax({
                url: `{{ route('admin.orders.index') }}/${orderId}`,
                type: 'GET',
                success: function(response) {
                    $('#orderDetailContent').html(response);
                },
                error: function() {
                    $('#orderDetailContent').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải chi tiết đơn hàng!</div>');
                }
            });
        });
    
        $(document).on('click', '.edit-order', function() {
            let orderId = $(this).data('id');
            $('#updateOrderId').val(orderId);
            $('#newStatus').val('');
            $('#statusNote').val('');
        });
    
        // Update status form
        $('#updateStatusForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                id: $('#updateOrderId').val(),
                status: $('#newStatus').val(),
                note: $('#statusNote').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };
            
            $.post('{{ route("admin.orders.update-status") }}', formData)
                .done(function(response) {
                    if (response.success) {
                        $('#updateStatusModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire({
                            title: 'Thành công!',
                            text: response.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .fail(function(xhr) {
                    const message = xhr.responseJSON?.message || 'Có lỗi xảy ra!';
                    Swal.fire({
                        title: 'Lỗi!',
                        text: message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        });
    
        $(document).on('click', '.delete-order', function() {
            let orderId = $(this).data('id');
            $('#del_order_id').val(orderId);
        });
    
    });
</script>

<!-- Initialize AJAX Form Handler -->
<script>
    $(document).ready(function() {
        // Initialize AJAX Form Handler for this page
        if (typeof AjaxFormHandler !== 'undefined') {
            AjaxFormHandler.init({
                table: 'table',
                forms: ['#updateStatusModal form', '#deleteOrderModal form']
            });
        }
    });
</script>
@endpush