@extends('layouts.admin')
@section('title', 'Quản lý nhân viên')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Danh sách nhân viên</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý nhân viên']]" />
    </div>

    <!-- Card con chứa table -->
    <div class="card m-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <!-- Trái -->
            <div class="flex-grow-1">
                <input type="search" id="userSearch"
                    class="form-control form-control-sm"
                    placeholder="Tìm kiếm nhân viên..."
                    style="max-width: 220px;">
            </div>

            <!-- Phải -->
            <div class="d-flex align-items-center">
                <div class="mr-2">
                    <select id="statusFilter" class="form-control form-control-sm mr-2">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="1">Active</option>
                        <option value="0">Blocked</option>
                    </select>
                </div>

                <div class="mr-2">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addUserModal">
                        <i class="fas fa-user-plus"></i> Thêm nhân viên
                    </button>
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
                <table class="table table-bordered table-striped align-middle w-100" id="usersTable">
                    <thead class="table-info">
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Quyền</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<x-admin.modal
    modal_id='addUserModal'
    title="Thêm nhân viên mới"
    url="{{ route('admin.users.store') }}"
    button_type="create"
    method="POST">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <label for="add_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add_name" name="name" required>
            <x-input-error name="name" />
        </div>
        <div class="col-12 col-md-6">
            <label for="add_email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="add_email" name="email" required>
            <x-input-error name="email" />
        </div>
        <div class="col-12 col-md-6">
            <label for="add_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="add_phone" name="phone" required maxlength="15">
            <x-input-error name="phone" />
        </div>
        <div class="col-12 col-md-6">
            <label for="add_password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="add_password" name="password" required>
            <x-input-error name="password" />
        </div>
        <div class="col-12 col-md-6">
            <label for="add_password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="add_password_confirmation" name="password_confirmation" required>
            <x-input-error name="password_confirmation" />
        </div>
        <div class="col-12 col-md-6">
            <label for="add_status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-control" id="add_status" name="status" required>
                <option value="1">✅ Hoạt động</option>
                <option value="0">🚫 Bị chặn</option>
            </select>
            <x-input-error name="status" />
        </div>
        <div class="col-12">
            <label for="add_address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
            <textarea class="form-control" id="add_address" name="address" rows="2" required maxlength="255"></textarea>
            <x-input-error name="address" />
        </div>
    </div>
</x-admin.modal>

<!-- Edit User Modal -->
<x-admin.modal
    modal_id='editUserModal'
    title="Cập nhật thông tin nhân viên"
    url="{{ route('admin.users.update') }}"
    button_type="update"
    method="PUT">
    <div class="row g-3">
        <input type="hidden" name="id" id="edit_user_id">
        <div class="col-12 col-md-6">
            <label for="edit_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_name" name="name" required>
            <x-input-error name="name" />
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="edit_email" name="email" required>
            <x-input-error name="email" />
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_phone" name="phone" required maxlength="15">
            <x-input-error name="phone" />
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-control" id="edit_status" name="status" required>
                <option value="1">✅ Hoạt động</option>
                <option value="0">🚫 Bị chặn</option>
            </select>
            <x-input-error name="status" />
        </div>
        <div class="col-12">
            <label for="edit_address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit_address" name="address" rows="2" required maxlength="255"></textarea>
            <x-input-error name="address" />
        </div>
        <div class="col-12">
            <label for="edit_role_id" class="form-label">Quyền <span class="text-danger">*</span></label>
            <select class="form-control" id="edit_role_id" name="role_id" required>
                <option value="1">👑 Admin</option>
                <option value="2">👨‍💼 Nhân viên</option>
                <option value="3">👤 Khách hàng</option>
            </select>
            <x-input-error name="role_id" />
        </div>
    </div>
</x-admin.modal>

<!-- Delete User Modal -->
<x-admin.modal
    modal_id='deleteUserModal'
    title="Xóa nhân viên"
    url="{{ route('admin.users.destroy') }}"
    button_type="delete"
    method="DELETE">
    <div class="mb-3 rounded-2">
        <input type="hidden" name="id" id="del_user_id">
        <label for="delete_user" class="form-label fw-bold">Bạn có chắc chắn muốn xóa nhân viên này? <br>Dữ liệu sẽ không thể khôi phục.</label>
    </div>
</x-admin.modal>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        window.table = $('#usersTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.users.data') }}",
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
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
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
                    data: 'role_badge',
                    name: 'role_id',
                    orderable: true,
                    searchable: false,
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
                lengthMenu: 'Hiển thị _MENU_ nhân viên',
                info: 'Hiển thị _START_ đến _END_ trong tổng _TOTAL_ nhân viên',
                infoEmpty: 'Không có dữ liệu',
                zeroRecords: '🔍Không tìm thấy nhân viên',
                infoFiltered: '(lọc từ tổng _MAX_ nhân viên)',
                loadingRecords: '<i class="fas fa-spinner fa-spin"></i> Đang tải...',
                emptyTable: 'Không có nhân viên nào để hiển thị.',
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

        $('#userSearch').on('keyup', function() {
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
        
        $(document).on('click', '.edit-user', function() {
            let userId = $(this).data('id');
            $('#edit_user_id').val(userId);

            // Load user data
            $.ajax({
                url: '/users/' + userId,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const user = response.user;
                        $('#edit_name').val(user.name);
                        $('#edit_email').val(user.email);
                        $('#edit_phone').val(user.phone);
                        $('#edit_address').val(user.address);
                        $('#edit_status').val(user.status);
                        $('#edit_role_id').val(user.role_id);
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Không thể tải thông tin nhân viên!',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });

        $(document).on('click', '.toggle-status', function() {
            let userId = $(this).data('id');
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
                        url: '/users/toggle-status',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            id: userId,
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
        
        $(document).on('click', '.delete-user', function() {
            let userId = $(this).data('id');
            $('#del_user_id').val(userId);
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
                forms: ['#addUserModal form', '#editUserModal form', '#deleteUserModal form']
            });
        }
    });
</script>
@endpush
