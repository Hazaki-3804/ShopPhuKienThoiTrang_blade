@extends('layouts.admin')
@section('title', 'Quản lý phí vận chuyển')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Quản lý phí vận chuyển</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý phí vận chuyển']]" />
    </div>

    <!-- Statistics Cards -->
    <div class="row mx-3 my-3">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_rules'] }}</h3>
                            <p class="mb-0">Tổng quy tắc</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-shipping-fast fa-2x opacity-75"></i>
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
                            <h3 class="mb-0">{{ $stats['active_rules'] }}</h3>
                            <p class="mb-0">Đang kích hoạt</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['free_shipping_rules'] }}</h3>
                            <p class="mb-0">Miễn phí ship</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-gift fa-2x opacity-75"></i>
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
                            <h3 class="mb-0">{{ $stats['local_rules'] }}</h3>
                            <p class="mb-0">Nội thành</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-map-marker-alt fa-2x opacity-75"></i>
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
                <input type="search" id="shippingFeeSearch"
                    class="form-control form-control-sm"
                    placeholder="Tìm kiếm quy tắc phí vận chuyển..."
                    style="max-width: 280px;">
            </div>

            <!-- Right side - Bulk actions and Add button -->
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-danger btn-sm mr-2" id="bulkDeleteBtn" style="display: none;" data-toggle="modal" data-target="#bulkDeleteModal">
                    <i class="fas fa-trash"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>

                <button type="button" class="btn btn-success btn-sm mr-2" data-toggle="modal" data-target="#addShippingFeeModal">
                    <i class="fas fa-plus"></i> Thêm quy tắc
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive no-scrollbar">
                <table class="table table-bordered table-striped align-middle w-100" id="shippingFeesTable">
                    <thead class="table-info">
                        <tr>
                            <th width="30" style="padding:0; text-align:center; vertical-align:middle;">
                                <div style="display:flex; justify-content:center; align-items:center; height:100%;">
                                    <input type="checkbox" id="selectAll" class="form-check-input" style="margin:0;">
                                </div>
                            </th>
                            <th width="50px">STT</th>
                            <th>Tên quy tắc</th>
                            <th>Khu vực</th>
                            <th>Khoảng cách</th>
                            <th>Phí ship</th>
                            <th>Đơn tối thiểu</th>
                            <th>Trạng thái</th>
                            <th width="120px">Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Shipping Fee Modal -->
<x-admin.modal
    modal_id='addShippingFeeModal'
    title="Thêm quy tắc phí vận chuyển"
    url="{{ route('admin.shipping-fees.store') }}"
    button_type="save"
    method="POST">
    @include('admin.shipping-fees.form')
</x-admin.modal>

<!-- Edit Shipping Fee Modal -->
<x-admin.modal
    modal_id='editShippingFeeModal'
    title="Chỉnh sửa quy tắc phí vận chuyển"
    url="{{ route('admin.shipping-fees.update') }}"
    button_type="update"
    method="PUT">
    <input type="hidden" name="id" id="edit-shipping-fee-id">
    @include('admin.shipping-fees.form', ['isEdit' => true])
</x-admin.modal>

<!-- Delete Shipping Fee Modal -->
<x-admin.modal
    modal_id='deleteShippingFeeModal'
    title="Xác nhận xóa"
    url="{{ route('admin.shipping-fees.destroy') }}"
    button_type="delete"
    method="DELETE">
    <input type="hidden" name="id" id="delete-shipping-fee-id">
    <p>Bạn có chắc chắn muốn xóa quy tắc <strong id="delete-shipping-fee-name"></strong>?</p>
    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
</x-admin.modal>

<!-- Bulk Delete Modal -->
<x-admin.modal
    modal_id='bulkDeleteModal'
    title="Xác nhận xóa nhiều"
    url="{{ route('admin.shipping-fees.destroy.multiple') }}"
    button_type="delete"
    method="DELETE">
    <input type="hidden" name="ids" id="bulk-delete-ids">
    <p>Bạn có chắc chắn muốn xóa <strong><span id="bulk-count">0</span></strong> quy tắc đã chọn?</p>
    <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Hành động này không thể hoàn tác!</p>
</x-admin.modal>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
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

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#shippingFeesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.shipping-fees.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'area_type_badge', name: 'area_type_badge', orderable: false },
            { data: 'distance_range', name: 'distance_range', orderable: false },
            { data: 'fee_display', name: 'fee_display', orderable: false },
            { data: 'min_order_display', name: 'min_order_display', orderable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']], // Sort by STT
        pageLength: 10,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/vi.json'
        }
    });

    // Custom search
    $('#shippingFeeSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Select all checkbox
    $('#selectAll').on('click', function() {
        const isChecked = $(this).prop('checked');
        $('.shipping-fee-checkbox').prop('checked', isChecked);
        updateBulkDeleteButton();
    });

    // Individual checkbox
    $(document).on('change', '.shipping-fee-checkbox', function() {
        updateBulkDeleteButton();
        const totalCheckboxes = $('.shipping-fee-checkbox').length;
        const checkedCheckboxes = $('.shipping-fee-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    function updateBulkDeleteButton() {
        const checkedCount = $('.shipping-fee-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkDeleteBtn').show();
            $('#selectedCount').text(checkedCount);
        } else {
            $('#bulkDeleteBtn').hide();
        }
    }

    // Edit shipping fee - Click event (primary method)
    $(document).on('click', '.edit-shipping-fee', function(e) {
        e.preventDefault();
        const data = $(this).data();
        
        console.log('Edit button clicked', data); // Debug
        
        $('#edit-shipping-fee-id').val(data.id);
        $('#edit-name').val(data.name);
        $('#edit-area_type').val(data.area_type);
        $('#edit-min_distance').val(data.min_distance);
        $('#edit-max_distance').val(data.max_distance || '');
        $('#edit-min_order_value').val(data.min_order_value);
        $('#edit-base_fee').val(data.base_fee);
        $('#edit-per_km_fee').val(data.per_km_fee);
        $('#edit-max_fee').val(data.max_fee || '');
        $('#edit-is_free_shipping').prop('checked', data.is_free_shipping == 1);
        $('#edit-priority').val(data.priority);
        $('#edit-status').val(data.status);
        $('#edit-description').val(data.description || '');
        
        toggleFreeShipping('edit');
        
        // Show modal - Compatible with both Bootstrap 4 and 5
        const editModal = document.getElementById('editShippingFeeModal');
        if (editModal) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = new bootstrap.Modal(editModal);
                bsModal.show();
            } else {
                $('#editShippingFeeModal').modal('show');
            }
        }
    });

    // Delete shipping fee - Click event (primary method)
    $(document).on('click', '.delete-shipping-fee', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        console.log('Delete button clicked', {id, name}); // Debug
        
        $('#delete-shipping-fee-id').val(id);
        $('#delete-shipping-fee-name').text(name);
        
        // Show modal - Compatible with both Bootstrap 4 and 5
        const deleteModal = document.getElementById('deleteShippingFeeModal');
        if (deleteModal) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = new bootstrap.Modal(deleteModal);
                bsModal.show();
            } else {
                $('#deleteShippingFeeModal').modal('show');
            }
        }
    });

    // Bulk delete
    $('#bulkDeleteBtn').on('click', function() {
        const selectedIds = [];
        $('.shipping-fee-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        $('#bulk-delete-ids').val(JSON.stringify(selectedIds));
        $('#bulk-count').text(selectedIds.length);
    });

    // Toggle free shipping fields
    function toggleFreeShipping(prefix = '') {
        const checkbox = $(`#${prefix ? prefix + '-' : ''}is_free_shipping`);
        const isChecked = checkbox.is(':checked');
        
        $(`#${prefix ? prefix + '-' : ''}base_fee`).prop('disabled', isChecked);
        $(`#${prefix ? prefix + '-' : ''}per_km_fee`).prop('disabled', isChecked);
        $(`#${prefix ? prefix + '-' : ''}max_fee`).prop('disabled', isChecked);
    }

    $('#is_free_shipping, #edit-is_free_shipping').on('change', function() {
        const prefix = $(this).attr('id').includes('edit') ? 'edit' : '';
        toggleFreeShipping(prefix);
    });

    // Initialize on page load
    toggleFreeShipping();
    toggleFreeShipping('edit');

    // Handle form submissions
    $('#addShippingFeeModal form, #editShippingFeeModal form, #deleteShippingFeeModal form, #bulkDeleteModal form').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $form = $(this);
        const url = $form.attr('action');
        const formData = $form.serialize();
        const modalId = $form.closest('.modal').attr('id');
        
        console.log('Form submitted:', {url, modalId, formData}); // Debug

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Success response:', response); // Debug
                
                // Close modal - Simple approach
                $(`#${modalId}`).hide();
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('padding-right', '');
                
                // Reload table
                table.ajax.reload(null, false);
                
                // Reset checkboxes
                $('.shipping-fee-checkbox').prop('checked', false);
                $('#selectAll').prop('checked', false);
                updateBulkDeleteButton();
                
                // Reset form
                $form[0].reset();
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: response.message || 'Thao tác thành công!',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                console.error('Error response:', xhr); // Debug
                
                let message = 'Có lỗi xảy ra!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('\n');
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

    // Reload table after operations
    window.addEventListener('shipping-fee-updated', function() {
        table.ajax.reload(null, false);
        $('.shipping-fee-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        updateBulkDeleteButton();
    });
});
</script>
@endpush
