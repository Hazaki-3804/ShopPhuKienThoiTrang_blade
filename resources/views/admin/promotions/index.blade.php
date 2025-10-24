@extends('layouts.admin')
@section('title', 'Quản lý chương trình khuyến mãi')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Quản lý chương trình khuyến mãi</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý khuyến mãi']]" />
    </div>

    <!-- Statistics Cards -->
    <div class="row mx-3 my-3">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_promotions'] }}</h3>
                            <p class="mb-0">Tổng chương trình</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-gift fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['active_promotions'] }}</h3>
                            <p class="mb-0">Đang hoạt động</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['upcoming_promotions'] }}</h3>
                            <p class="mb-0">Sắp diễn ra</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['expired_promotions'] }}</h3>
                            <p class="mb-0">Đã hết hạn</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-calendar-times fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card m-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <!-- Left side - Search -->
            <div class="flex-grow-1">
                <input type="search" id="promotionSearch"
                    class="form-control form-control-sm"
                    placeholder="Tìm kiếm chương trình khuyến mãi..."
                    style="max-width: 280px;">
            </div>

            <!-- Right side - Bulk actions and Add button -->
            <div class="d-flex align-items-center">
                <!-- Bulk delete button (hidden by default) -->
                @if(auth()->user()->can('delete promotions'))
                <button type="button" class="btn btn-danger btn-sm mr-2" id="bulkDeleteBtn" style="display: none;" data-toggle="modal" data-target="#bulkDeleteModal">
                    <i class="fas fa-trash"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>
                @endif
                
                @if(auth()->user()->can('create promotions'))
                <a href="{{ route('admin.promotions.create') }}" class="btn btn-success btn-sm mr-2">
                    <i class="fas fa-plus"></i> Thêm chương trình
                </a>
                @endif

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
                <table class="table table-bordered table-striped align-middle w-100" id="promotionsTable">
                    <thead class="table-info">
                        <tr>
                            <th width="30" style="padding:0; text-align:center; vertical-align:middle;">
                                <div style="display:flex; justify-content:center; align-items:center; height:100%;">
                                    <input type="checkbox" id="selectAll" class="form-check-input" style="margin:0;">
                                </div>
                            </th>
                            <th width="50px">STT</th>
                            <th>Mã KM</th>
                            <th>Mô tả</th>
                            <th>Giảm giá</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Số sản phẩm</th>
                            <th>Số lượng</th>
                            <th width="120px">Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Promotion Modal -->
<x-admin.modal
    modal_id='deletePromotionModal'
    title="Xác nhận xóa"
    url="{{ route('admin.promotions.destroy') }}"
    button_type="delete"
    method="DELETE">
    <input type="hidden" name="id" id="delete-promotion-id">
    <p>Bạn có chắc chắn muốn xóa chương trình khuyến mãi <strong id="delete-promotion-code"></strong>?</p>
    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
</x-admin.modal>

<!-- Bulk Delete Modal -->
<x-admin.modal
    modal_id='bulkDeleteModal'
    title="Xác nhận xóa nhiều"
    url="{{ route('admin.promotions.destroy.multiple') }}"
    button_type="delete"
    method="DELETE">
    <input type="hidden" name="ids" id="bulk-delete-ids">
    <p>Bạn có chắc chắn muốn xóa <strong><span id="bulk-count">0</span></strong> chương trình khuyến mãi đã chọn?</p>
    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
</x-admin.modal>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    window.promotionsTable = $('#promotionsTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.promotions.data') }}",
                type: 'GET',
                data: function(d) {
                    d.category_id = $('#categoryFilter').val();
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
             columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'code', name: 'code' },
            { data: 'description', name: 'description' },
            { data: 'discount_display', name: 'discount_display', orderable: false },
            { data: 'date_range', name: 'date_range', orderable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'products_count', name: 'products_count', orderable: false },
            { data: 'quantity_display', name: 'quantity_display', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
            order: [
                [2, 'asc']
            ],
            columnDefs: [
                {
                    targets: 0, // Cột checkbox
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    targets: 1, // Cột STT
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
            ],
            responsive: true,
            language: {
                search: 'Tìm kiếm:',
                lengthMenu: 'Hiển thị _MENU_ sản phẩm',
                info: 'Hiển thị _START_ đến _END_ trong tổng _TOTAL_ sản phẩm',
                infoEmpty: 'Không có dữ liệu',
                zeroRecords: '🔍 Không tìm thấy sản phẩm',
                infoFiltered: '(lọc từ tổng _MAX_ sản phẩm)',
                loadingRecords: '<i class="fas fa-spinner fa-spin"></i> Đang tải...',
                emptyTable: 'Không có sản phẩm nào để hiển thị.',
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
    const typingDelay = 500;
    // Custom search
    $('#promotionSearch').on('keyup', function() {
        clearTimeout(typingTimer);
        const value = this.value;

        typingTimer = setTimeout(function() {
            promotionsTable.search(value).draw();
        }, typingDelay);
    });

    // Select all checkbox
    $('#selectAll').on('click', function() {
        const isChecked = $(this).prop('checked');
        $('.promotion-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Individual checkbox
    $(document).on('change', '.promotion-checkbox', function() {
        updateBulkDeleteButton();
        
        const totalCheckboxes = $('.promotion-checkbox').length;
        const checkedCheckboxes = $('.promotion-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    function updateBulkDeleteButton() {
        const checkedCount = $('.promotion-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteBtn').show();
            $('#selectedCount').text(checkedCount);
        } else {
            $('#bulkDeleteBtn').hide();
        }
    }

    // Delete single promotion
    $(document).on('click', '.delete-promotion', function() {
        const id = $(this).data('id');
        const code = $(this).data('code');
        $('#delete-promotion-id').val(id);
        $('#delete-promotion-code').text(code);
        $('#deletePromotionModal').modal('show');
    });

    // Bulk delete
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = [];
        $('.promotion-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        $('#bulk-delete-ids').val(JSON.stringify(selectedIds));
        $('#bulk-count').text(selectedIds.length);
    });

    // Export buttons - Direct download
       // Export buttons
    $('#btn-excel').on('click', function(e) {
        e.preventDefault();
        promotionsTable.button('.buttons-excel').trigger();
    });
    $('#btn-csv').on('click', function(e) {
        e.preventDefault();
        promotionsTable.button('.buttons-csv').trigger();
    });
    $('#btn-pdf').on('click', function(e) {
        e.preventDefault();
        categoriesTable.button('.buttons-pdf').trigger();
    });
    $('#btn-print').on('click', function(e) {
        e.preventDefault();
        categoriesTable.button('.buttons-print').trigger();
    });

    // Initialize AJAX Form Handler for modals
    if (typeof AjaxFormHandler !== 'undefined') {
        AjaxFormHandler.init({
            table: 'promotionsTable',
            forms: ['#deletePromotionModal form', '#bulkDeleteModal form']
        });
    }

    // Handle form submissions manually if AjaxFormHandler is not available
    $('#deletePromotionModal form, #bulkDeleteModal form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const url = $form.attr('action');
        const modalId = $form.closest('.modal').attr('id');
        
        // Prepare form data
        const formData = new FormData($form[0]);
        
        // Convert FormData to object for jQuery
        const dataObject = {};
        formData.forEach((value, key) => {
            dataObject[key] = value;
        });
        
        // Add _method for Laravel to recognize DELETE request
        dataObject['_method'] = 'DELETE';
        
        console.log('Sending data:', dataObject); // Debug log

        $.ajax({
            url: url,
            type: 'POST', // Always use POST for jQuery AJAX
            data: dataObject,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Close modal - Force close all modals
                $(`#${modalId}`).modal('hide');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                
                // Reload table
                promotionsTable.ajax.reload(null, false);
                
                // Reset checkboxes
                $('.promotion-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                updateBulkDeleteButton();
                
                // Show success Toast notification
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'success',
                        title: response.message || 'Thao tác thành công!'
                    }).then(() => {
                        // Force reload page to update statistics
                        location.reload();
                    });
                } else {
                    alert(response.message);
                    location.reload();
                }
            },
            error: function(xhr) {
                let message = 'Có lỗi xảy ra!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                }
                
                // Show error Toast notification
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        html: message
                    });
                } else {
                    alert(message);
                }
            }
        });
    });

    // Reload table after successful operations
    window.addEventListener('promotion-updated', function() {
        promotionsTable.ajax.reload(null, false);
        $('.promotion-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkDeleteButton();
    });
});
</script>
@endpush
