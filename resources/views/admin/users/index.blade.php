@extends('layouts.admin')
@section('title', 'Quản lý nhân viên')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-4">
        <h4 class="fw-semibold m-0">Quản lý nhân viên</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý nhân viên']]" />
    </div>

    <!-- Statistics Cards -->
    <div class="row mx-3 my-3">
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_users'] }}</h3>
                            <p class="mb-0 small">Tổng nhân viên</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['active_users'] }}</h3>
                            <p class="mb-0 small">Đang hoạt động</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-user-check fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['blocked_users'] }}</h3>
                            <p class="mb-0 small">Bị khóa</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-user-slash fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['new_staff_users'] }}</h3>
                            <p class="mb-0 small">Nhân viên mới</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-user-tie fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                        <option value="1">Mở tài khoản</option>
                        <option value="0">Khóa tài khoản</option>
                    </select>
                </div>

                <div class="mr-2">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addUserModal">
                        <i class="fas fa-user-plus mr-1"></i>Thêm nhân viên
                    </button>
                </div>

                <div class="mr-2">
                    <a href="{{ route('admin.users.import') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-file-import mr-1"></i>Import Excel
                    </a>
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
                <table class="table table-bordered table-striped align-middle w-100" id="usersTable">
                    <thead class="table-info">
                        <tr>
                            <th>STT</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Quyền</th>
                            <th>Trạng thái</th>
                            @canany(['create staffs', 'edit staffs', 'delete staffs', 'lock/unlock staffs'])
                            <th>Thao tác</th>
                            @endcanany
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
            <div class="text-danger mt-1" id="add_name_error" style="font-size: 12px; display: none;"></div>
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
            <label for="add_status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-control" id="add_status" name="status" required>
                <option value="1">✅ Hoạt động</option>
                <option value="0">🚫 Bị chặn</option>
            </select>
            <div class="text-danger mt-1" id="add_status_error" style="font-size: 12px; display: none;"></div>
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
            <textarea class="form-control" id="add_address" name="address" rows="2" required maxlength="255"></textarea>
            <div class="text-danger mt-1" id="add_address_error" style="font-size: 12px; display: none;"></div>
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
            <div class="text-danger mt-1" id="edit_name_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="edit_email" name="email" required>
            <div class="text-danger mt-1" id="edit_email_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="edit_phone" name="phone" required maxlength="15">
            <div class="text-danger mt-1" id="edit_phone_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12 col-md-6">
            <label for="edit_status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-control" id="edit_status" name="status" required>
                <option value="1">✅ Hoạt động</option>
                <option value="0">🚫 Bị chặn</option>
            </select>
            <div class="text-danger mt-1" id="edit_status_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12">
            <label for="edit_address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
            <textarea class="form-control" id="edit_address" name="address" rows="2" maxlength="255"></textarea>
            <div class="text-danger mt-1" id="edit_address_error" style="font-size: 12px; display: none;"></div>
        </div>
        <div class="col-12">
            <label for="edit_role_id" class="form-label">Quyền <span class="text-danger">*</span></label>
            <select class="form-control" id="edit_role_id" name="role_id" required>
                <option value="1">👑 Admin</option>
                <option value="2">👨‍💼 Nhân viên</option>
                <option value="3">👤 Khách hàng</option>
            </select>
            <div class="text-danger mt-1" id="edit_role_id_error" style="font-size: 12px; display: none;"></div>
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
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let permissions = @canany(['create staffs', 'edit staffs', 'delete staffs', 'lock/unlock staffs']) true @else false @endcanany;
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
                        columns: ':visible:not(:first-child):not(:last-child)'
                    }
                },
                {
                    extend: 'csvHtml5',
                    className: 'buttons-csv',
                    bom: true,
                    charset: 'utf-8',
                    exportOptions: {
                        columns: ':visible:not(:first-child):not(:last-child)'  
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'buttons-pdf',
                    exportOptions: {
                        columns: ':visible:not(:first-child):not(:last-child)'                      
                    }
                },
            ],
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'user_info',
                    name: 'name',
                    width: '15%'
                },
                {
                    data: 'email',
                    name: 'email',
                    width: '15%'
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
                    width: '8%',
                    orderable: false,
                    searchable: false,
                    visible: permissions
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
        
        // Function to update statistics (make it global)
        window.updateUserStats = function() {
            $.ajax({
                url: '{{ route("admin.users.stats") }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const stats = response.stats;
                        
                        // Update each statistic card with animation
                        $('.card.bg-primary .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.total_users).fadeIn(200);
                        });
                        $('.card.bg-success .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.active_users).fadeIn(200);
                        });
                        $('.card.bg-danger .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.blocked_users).fadeIn(200);
                        });
                        // $('.card.bg-warning .card-body h3').fadeOut(200, function() {
                        //     $(this).text(stats.admin_users).fadeIn(200);
                        // });
                        $('.card.bg-info .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.new_staff_users).fadeIn(200);
                        });
                        // $('.card.bg-secondary .card-body h3').fadeOut(200, function() {
                        //     $(this).text(stats.customer_users).fadeIn(200);
                        // });
                    }
                },
                error: function(xhr) {
                    console.error('Error updating user stats:', xhr);
                }
            });
        }
        updateUserStats(); // gọi khi vừa load trang

        $(document).on('click', '.edit-user', function() {
            let userId = $(this).data('id');
            $('#edit_user_id').val(userId);

            // Load user data
            $.ajax({
                url: '/admin/users/' + userId,
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
                        url: '{{ route("admin.users.toggle-status") }}',
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

        // Clear validation errors when modals are opened
        $('#addUserModal').on('show.bs.modal', function() {
            if (typeof AjaxFormHandler !== 'undefined') {
                AjaxFormHandler.clearFieldErrors('addUserModal');
            }
        });

        $('#editUserModal').on('show.bs.modal', function() {
            if (typeof AjaxFormHandler !== 'undefined') {
                AjaxFormHandler.clearFieldErrors('editUserModal');
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
                forms: ['#addUserModal form', '#editUserModal form', '#deleteUserModal form'],
                onSuccess: function(response) {
                    // Update statistics after successful operations
                    updateUserStats();
                }
            });
        }
    });
</script>
@endpush
