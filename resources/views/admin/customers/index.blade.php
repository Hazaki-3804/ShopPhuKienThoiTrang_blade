@extends('layouts.admin')
@section('title', 'Quản lý khách hàng')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-4">
        <h4 class="fw-semibold m-0">Danh sách khách hàng</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý khách hàng']]" />
    </div>

    <!-- Card con chứa table -->
    <div class="card m-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <!-- Trái -->
            <div class="flex-grow-1">
                <input type="search" id="customerSearch"
                    class="form-control form-control-sm"
                    placeholder="Tìm kiếm khách hàng..."
                    style="max-width: 220px;">
            </div>

            <!-- Phải -->
            <div class="d-flex align-items-center">
                <div class="mr-2">
                    <select id="statusFilter" class="form-control form-control-sm">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="1">✅ Mở tài khoản</option>
                        <option value="0">🚫 Khóa tài khoản</option>
                    </select>
                </div>
                <div class="mr-2">
                    @if(auth()->user()->can('create customers'))
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addCustomerModal">
                        <i class="fas fa-user-plus mr-1"></i> Thêm khách hàng
                    </button>
                    @endif
                </div>
                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-export mr-1"></i> Export
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                        <a class="dropdown-item" href="#" id="btn-excel"><i class="fas fa-file-excel text-success"></i> Excel</a>
                        <a class="dropdown-item" href="#" id="btn-csv"><i class="fas fa-file-csv text-info"></i> CSV</a>
                        <a class="dropdown-item" href="#" id="btn-pdf"><i class="fas fa-file-pdf text-danger"></i> PDF</a>
                        <a class="dropdown-item" href="#" id="btn-print"><i class="fas fa-print text-primary"></i> Print</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive no-scrollbar">
                <table class="table table-bordered table-striped align-middle w-100" id="customersTable">
                    <thead class="table-info">
                        <tr>
                            <th>ID</th>
                            <th>Khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Add Customer Modal -->
<x-admin.modal
    modal_id='addCustomerModal'
    title="Thêm khách hàng mới"
    url="{{ route('admin.customers.store') }}"
    button_type="create"
    method="POST">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="add_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add_name" name="name" required>
            <div class="text-danger mt-1" id="add_name_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="add_username" class="form-label">Tên người dùng</label>
            <input type="text" class="form-control" id="add_username" name="username" maxlength="50">
            <div class="text-danger mt-1" id="add_username_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="add_email" name="email" required>
            <div class="text-danger mt-1" id="add_email_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="add_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add_phone" name="phone" required maxlength="15">
            <div class="text-danger mt-1" id="add_phone_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="add_password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="add_password" name="password" required>
            <div class="text-danger mt-1" id="add_password_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="add_password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="add_password_confirmation" name="password_confirmation" required>
            <div class="text-danger mt-1" id="add_password_confirmation_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12">
            <label for="add_address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
            <textarea class="form-control" id="add_address" name="address" rows="2" maxlength="255"></textarea>
            <div class="text-danger mt-1" id="add_address_error" style="font-size: 12px; display: none;"></div>
        </div>
        <!-- <div class="col-12 col-md-6">
            <label for="add_ward_id" class="form-label">Xã/Phường <span class="text-danger">*</span></label>
            <select class="form-control" id="add_ward_id" name="ward_id" required>
                <option value="">-- Chọn xã/phường --</option>
            </select>
            <div class="text-danger mt-1" id="add_ward_id_error" style="font-size: 12px; display: none;"></div>
        </div> -->
        <div class="col-12 col-md-6">
            <label for="add_status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-control" id="add_status" name="status" required>
                <option value="1">✅ Mở tài khoản</option>
                <option value="0">🚫 Khóa tài khoản</option>
            </select>
            <x-input-error name="status" />
            <div class="text-danger mt-1" id="add_status_error" style="font-size: 12px; display: none;"></div>
        </div>
    </div>
</x-admin.modal>

<!-- Edit Customer Modal -->
<x-admin.modal
    modal_id='editCustomerModal'
    title="Cập nhật thông tin khách hàng"
    url="{{ route('admin.customers.update', ['id' => 0]) }}"
    button_type="update"
    method="PUT">
    <div class="row g-3">
        <input type="hidden" name="id" id="edit_customer_id">
        <div class="col-12 col-md-6">
            <label for="edit_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_name" name="name" required>
            <x-input-error name="name" />
            <div class="text-danger mt-1" id="edit_name_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_username" class="form-label">Tên người dùng</label>
            <input type="text" class="form-control" id="edit_username" name="username" maxlength="50">
            <x-input-error name="username" />
            <div class="text-danger mt-1" id="edit_username_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="edit_email" name="email" required>
            <x-input-error name="email" />
            <div class="text-danger mt-1" id="edit_email_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_phone" name="phone" required maxlength="15">
            <x-input-error name="phone" />
            <div class="text-danger mt-1" id="edit_phone_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12">
            <label for="edit_address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit_address" name="address" rows="2" required maxlength="255"></textarea>
            <x-input-error name="address" />
            <div class="text-danger mt-1" id="edit_address_error" style="font-size: 12px; display: none;"></div>
        </div>
                <!-- <div class="col-12 col-md-6">
            <label for="edit_ward_id" class="form-label">Xã/Phường <span class="text-danger">*</span></label>
            <select class="form-control" id="edit_ward_id" name="ward_id" required>
                <option value="">-- Chọn xã/phường --</option>
            </select>
            <div class="text-danger mt-1" id="edit_ward_id_error" style="font-size: 12px; display: none;"></div>
        </div> -->
        <div class="col-12 col-md-6">
            <label for="edit_status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-control" id="edit_status" name="status" required>
                <option value="1">✅ Mở tài khoản</option>
                <option value="0">🚫 Khóa tài khoản</option>
            </select>
            <x-input-error name="status" />
            <div class="text-danger mt-1" id="edit_status_error" style="font-size: 12px; display: none;"></div>
        </div>
    </div>
</x-admin.modal>
<x-admin.modal
    modal_id='deleteCustomerModal'
    title="Xóa khách hàng"
    url="{{ route('admin.customers.destroy') }}"
    button_type="delete"
    method="DELETE">
    <div class="mb-3 rounded-2">
        <input type="hidden" name="id" id="del_customer_id">
        <label for="delete_customer" class="form-label fw-bold">
            Bạn có chắc chắn muốn xóa khách hàng này? 
            <br>Dữ liệu sẽ không thể khôi phục.
        </label>
        <div class="text-danger mt-1"><strong>Lưu ý:</strong> Khách hàng có đơn hàng sẽ không thể xóa!</div>
    </div>
</x-admin.modal>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
<style>
/* Validation error styles */
.form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.form-control.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

/* Error message styling */
[id$="_error"] {
    font-weight: 500;
}

[id$="_error"] i {
    margin-right: 4px;
}
</style>
@endpush
@push('scripts')
<script>
    $(document).ready(function() {
        window.table = $('#customersTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.customers.data') }}",
                type: 'GET',
                data: function(d) {
                    d.status = $('#statusFilter').val();
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
                    data: 'customer_info',
                    name: 'customer_info',
                    orderable: false,
                    searchable: true
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'address',
                    name: 'address'
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
                lengthMenu: 'Hiển thị _MENU_ khách hàng',
                info: 'Hiển thị _START_ đến _END_ trong tổng _TOTAL_ khách hàng',
                infoEmpty: 'Không có dữ liệu',
                zeroRecords: '🔍Không tìm thấy khách hàng',
                infoFiltered: '(lọc từ tổng _MAX_ khách hàng)',
                loadingRecords: '<i class="fas fa-spinner fa-spin"></i> Đang tải...',
                emptyTable: 'Không có khách hàng nào để hiển thị.',
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

        $('#customerSearch').on('keyup', function() {
            clearTimeout(typingTimer);
            const value = this.value;

            typingTimer = setTimeout(function() {
                table.search(value).draw();
            }, typingDelay);
        });
        $('#statusFilter').on('change', function() {
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
        $(document).on('click', '.edit-customer', function() {
            let customerId = $(this).data('id');
            $('#edit_customer_id').val(customerId);

            // Update form action URL with customer ID
            const form = $('#editCustomerModal form');
            const baseUrl = "{{ route('admin.customers.update', ['id' => ':id']) }}";
            const newUrl = baseUrl.replace(':id', customerId);
            form.attr('action', newUrl);

            // Load customer data
            $.ajax({
                url: '/admin/customers/' + customerId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const customer = response.customer;
                        $('#edit_name').val(customer.name);
                        $('#edit_username').val(customer.username);
                        $('#edit_email').val(customer.email);
                        $('#edit_phone').val(customer.phone);
                        $('#edit_address').val(customer.address);
                        $('#edit_ward_id').val(customer.ward_id);
                        $('#edit_status').val(customer.status);
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Không thể tải thông tin khách hàng!',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        $(document).on('click', '.toggle-status', function() {
            let customerId = $(this).data('id');
            let newStatus = $(this).data('status');
            let actionText = newStatus == 1 ? 'mở khóa' : 'khóa';
            let confirmTitle = newStatus == 1 ? 'Mở khóa tài khoản?' : 'Khóa tài khoản?';
            let confirmText = `Bạn có chắc chắn muốn ${actionText} tài khoản này?`;

            Swal.fire({
                title: confirmTitle,
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: newStatus == 1 ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: newStatus == 1 ? 'Mở khóa' : 'Khóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/customers/toggle-status',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: customerId,
                            status: newStatus
                        },
                        success: function(response) {
                            if (response.success) {
                                table.ajax.reload();
                                Swal.fire({
                                    title: 'Thành công!',
                                    text: response.message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có lỗi xảy ra khi thay đổi trạng thái tài khoản!',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        });
        $(document).on('click', '.delete-customer', function() {
            let customerId = $(this).data('id');
            $('#del_customer_id').val(customerId);
        });

        // Clear validation errors when modals are opened
        $('#addCustomerModal').on('show.bs.modal', function() {
            if (typeof AjaxFormHandler !== 'undefined') {
                AjaxFormHandler.clearFieldErrors('addCustomerModal');
            }
        });

        $('#editCustomerModal').on('show.bs.modal', function() {
            if (typeof AjaxFormHandler !== 'undefined') {
                AjaxFormHandler.clearFieldErrors('editCustomerModal');
            }
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
                forms: ['#addCustomerModal form', '#editCustomerModal form', '#deleteCustomerModal form']
            });
        }
    });
</script>
@endpush