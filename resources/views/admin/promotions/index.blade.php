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
                <button type="button" class="btn btn-danger btn-sm mr-2" id="bulkDeleteBtn" style="display: none;" data-toggle="modal" data-target="#bulkDeleteModal">
                    <i class="fas fa-trash"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>

                <a href="{{ route('admin.promotions.create') }}" class="btn btn-success btn-sm mr-2">
                    <i class="fas fa-plus"></i> Thêm chương trình
                </a>

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
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#promotionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.promotions.data") }}',
            type: 'GET'
        },
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
        order: [[1, 'desc']],
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        },
        dom: 'Bfrtip',
        buttons: []
    });

    // Custom search
    $('#promotionSearch').on('keyup', function() {
        table.search(this.value).draw();
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
    $('#btn-excel').on('click', function(e) {
        e.preventDefault();
        window.location.href = '{{ route("admin.promotions.export.excel") }}';
    });

    $('#btn-csv').on('click', function(e) {
        e.preventDefault();
        window.location.href = '{{ route("admin.promotions.export.excel") }}';
    });

    $('#btn-pdf').on('click', function(e) {
        e.preventDefault();
        // For now, use print functionality
        window.print();
    });

    $('#btn-print').on('click', function(e) {
        e.preventDefault();
        window.print();
    });

    // Initialize AJAX Form Handler for modals
    if (typeof AjaxFormHandler !== 'undefined') {
        AjaxFormHandler.init({
            table: 'table',
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
                table.ajax.reload(null, false);
                
                // Reset checkboxes
                $('.promotion-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                updateBulkDeleteButton();
                
                // Show success message
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
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
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: message,
                        showConfirmButton: true
                    });
                } else {
                    alert(message);
                }
            }
        });
    });

    // Reload table after successful operations
    window.addEventListener('promotion-updated', function() {
        table.ajax.reload(null, false);
        $('.promotion-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkDeleteButton();
    });
});
</script>
@endpush
