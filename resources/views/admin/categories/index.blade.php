@extends('layouts.admin')
@section('title', 'Quản lý danh mục')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-4">
        <h4 class="fw-semibold m-0">Quản lý danh mục sản phẩm</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý danh mục']]" />
    </div>

    <!-- Statistics Cards -->
    <div class="row mx-3 my-3">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_categories'] }}</h3>
                            <p class="mb-0">Tổng danh mục</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-tags fa-2x opacity-75"></i>
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
                            <h3 class="mb-0">{{ $stats['categories_with_products'] }}</h3>
                            <p class="mb-0">Có sản phẩm</p>
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
                            <h3 class="mb-0">{{ $stats['categories_without_products'] }}</h3>
                            <p class="mb-0">Chưa có sản phẩm</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
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
                            <h3 class="mb-0">{{ $stats['recent_categories'] }}</h3>
                            <p class="mb-0">Mới trong tuần</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-calendar-plus fa-2x opacity-75"></i>
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
                <input type="search" id="categorySearch"
                    class="form-control form-control-sm"
                    placeholder="Tìm kiếm danh mục..."
                    style="max-width: 220px;">
            </div>

            <!-- Right side - Bulk actions, Add button and Export -->
            <div class="btn-group">
                <!-- Bulk delete button (hidden by default) -->
                @if(auth()->user()->can('delete categories'))
                <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;" data-toggle="modal" data-target="#bulkDeleteModal">
                    <i class="fas fa-trash"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>
                @endif

                @if(auth()->user()->can('create categories'))
                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Thêm danh mục
                </button>
                @endif

                <div class="dropdown">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="exportDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-export"></i> Export
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
                <table class="table table-bordered table-striped align-middle w-100" id="categoriesTable">
                    <thead class="table-info">
                        <tr>
                            <th width="30" style="padding:0; text-align:center; vertical-align:middle;">
                                <div style="display:flex; justify-content:center; align-items:center; height:100%;">
                                    <input type="checkbox" id="selectAll" class="form-check-input" style="margin:0;">
                                </div>
                            </th>
                            <th width="50px">STT</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th>Slug</th>
                            <th>Số sản phẩm</th>
                            <th>Ngày tạo</th>
                            <th width="120px">Hành động</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<x-admin.modal
    modal_id='addCategoryModal'
    title="Thêm danh mục mới"
    url="{{ route('admin.categories.store') }}"
    button_type="save"
    method="POST">
    <div class="mb-3">
        <label for="add_name" class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="add_name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="add_description" class="form-label fw-bold">Mô tả</label>
        <textarea class="form-control" id="add_description" name="description" rows="3" placeholder="Mô tả về danh mục..."></textarea>
    </div>
</x-admin.modal>

<!-- Edit Category Modal -->
<x-admin.modal
    modal_id='editCategoryModal'
    title="Cập nhật danh mục"
    url="{{ route('admin.categories.update') }}"
    button_type="update"
    method="PUT">
    <div class="mb-3">
        <input type="hidden" name="id" id="edit_category_id">
        <label for="edit_name" class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="edit_name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="edit_description" class="form-label fw-bold">Mô tả</label>
        <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Mô tả về danh mục..."></textarea>
    </div>
</x-admin.modal>

<!-- Delete Category Modal -->
<x-admin.modal
    modal_id='deleteCategoryModal'
    title="Xóa danh mục"
    url="{{ route('admin.categories.destroy') }}"
    button_type="delete"
    method="DELETE">
    <div class="mb-3">
        <input type="hidden" name="id" id="del_category_id">
        <p class="fw-bold">Bạn có chắc chắn muốn xóa danh mục "<span id="del_category_name" class="text-danger"></span>"?</p>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Lưu ý:</strong> Không thể xóa danh mục đang có sản phẩm. Dữ liệu sẽ không thể khôi phục.
        </div>
    </div>
</x-admin.modal>

<!-- Bulk Delete Modal -->
<x-admin.modal
    modal_id='bulkDeleteModal'
    title="Xóa nhiều danh mục"
    url="{{ route('admin.categories.destroy.multiple') }}"
    button_type="delete"
    method="DELETE">
    <div class="mb-3">
        <p class="fw-bold">Bạn có chắc chắn muốn xóa <span id="bulkDeleteCount" class="text-danger"></span> danh mục đã chọn?</p>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Lưu ý:</strong> Không thể xóa danh mục đang có sản phẩm. Dữ liệu sẽ không thể khôi phục.
        </div>
        <div id="selectedCategoriesList" class="mt-2">
            <!-- Danh sách các danh mục được chọn sẽ hiển thị ở đây -->
        </div>
    </div>
</x-admin.modal>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        window.categoriesTable = $('#categoriesTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.categories.data') }}",
                type: 'GET'
            },
            dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                "t" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'excelHtml5',
                    name: 'excel-custom',
                    bom: true,
                    charset: 'utf-8',
                    title: 'Danh sách danh mục sản phẩm',
                    filename: 'Danh_muc_san_pham_' + moment().format('DD-MM-YYYY_HHmmss'),
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
                    filename: 'Danh_muc_san_pham_' + moment().format('DD-MM-YYYY_HHmmss'),
                    exportOptions: {
                        columns: ':visible:not(:first-child):not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'buttons-pdf',
                    title: 'Danh sách danh mục sản phẩm',
                    filename: 'Danh_muc_san_pham_' + moment().format('DD-MM-YYYY_HHmmss'),
                    exportOptions: {
                        columns: ':visible:not(:first-child):not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    className: 'buttons-print',
                    title: 'Danh sách danh mục sản phẩm',
                    exportOptions: {
                        columns: ':visible:not(:first-child):not(:last-child)'
                    }
                }
            ],
            columns: [{
                    data: 'checkbox',
                    name: 'checkbox',
                    width: '30px'
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    width: '50px'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'description',
                    name: 'description',
                    render: function(data) {
                        if (!data) return '<em class="text-muted">Chưa có mô tả</em>';
                        return data.length > 50 ? data.substring(0, 50) + '...' : data;
                    }
                },
                {
                    data: 'slug',
                    name: 'slug',
                    render: function(data) {
                        return '<code>' + data + '</code>';
                    }
                },
                {
                    data: 'products_count',
                    name: 'products_count',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'created_date',
                    name: 'created_at',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    width: '120px'
                }
            ],
            order: [
                [2, 'asc']
            ],
            columnDefs: [{
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
                {
                    targets: 7, // Cột hành động
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            responsive: true,
            language: {
                search: 'Tìm kiếm:',
                lengthMenu: 'Hiển thị _MENU_ danh mục',
                info: 'Hiển thị _START_ đến _END_ trong tổng _TOTAL_ danh mục',
                infoEmpty: 'Không có dữ liệu',
                zeroRecords: '🔍 Không tìm thấy danh mục',
                infoFiltered: '(lọc từ tổng _MAX_ danh mục)',
                loadingRecords: '<i class="fas fa-spinner fa-spin"></i> Đang tải...',
                emptyTable: 'Không có danh mục nào để hiển thị.',
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

        // Search functionality
        let typingTimer;
        const typingDelay = 500;

        $('#categorySearch').on('keyup', function() {
            clearTimeout(typingTimer);
            const value = this.value;

            typingTimer = setTimeout(function() {
                categoriesTable.search(value).draw();
            }, typingDelay);
        });

        // Export buttons
        $('#btn-excel').on('click', function(e) {
            e.preventDefault();
            categoriesTable.button('.buttons-excel').trigger();
        });
        $('#btn-csv').on('click', function(e) {
            e.preventDefault();
            categoriesTable.button('.buttons-csv').trigger();
        });
        $('#btn-pdf').on('click', function(e) {
            e.preventDefault();
            categoriesTable.button('.buttons-pdf').trigger();
        });
        $('#btn-print').on('click', function(e) {
            e.preventDefault();
            categoriesTable.button('.buttons-print').trigger();
        });

        // Modal handlers
        $(document).on('click', '.edit-category', function() {
            let categoryId = $(this).data('id');
            let categoryName = $(this).data('name');
            let categoryDescription = $(this).data('description');

            $('#edit_category_id').val(categoryId);
            $('#edit_name').val(categoryName);
            $('#edit_description').val(categoryDescription || '');
        });

        $(document).on('click', '.delete-category', function() {
            let categoryId = $(this).data('id');
            let categoryName = $(this).data('name');

            $('#del_category_id').val(categoryId);
            $('#del_category_name').text(categoryName);
        });

        // Reset form when add modal is opened
        $('#addCategoryModal').on('show.bs.modal', function() {
            $(this).find('form')[0].reset();
        });

        // Function to update statistics (make it global)
        window.updateStats = function() {
            $.ajax({
                url: '{{ route("admin.categories.stats") }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const stats = response.stats;

                        // Update each statistic card with animation
                        $('.card.bg-primary .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.total_categories).fadeIn(200);
                        });
                        $('.card.bg-success .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.categories_with_products).fadeIn(200);
                        });
                        $('.card.bg-warning .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.categories_without_products).fadeIn(200);
                        });
                        $('.card.bg-info .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.recent_categories).fadeIn(200);
                        });

                        // Add a subtle pulse animation to show the update
                        $('.card').addClass('animate__animated animate__pulse');
                        setTimeout(function() {
                            $('.card').removeClass('animate__animated animate__pulse');
                        }, 600);
                    }
                },
                error: function(xhr) {
                    console.error('Error updating stats:', xhr);
                }
            });
        }
        updateStats(); // gọi khi vừa load trang

        // Checkbox functionality
        let selectedCategories = [];

        // Select all checkbox
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.category-checkbox').prop('checked', isChecked);
            updateSelectedCategories();
        });

        // Individual checkbox
        $(document).on('change', '.category-checkbox', function() {
            updateSelectedCategories();

            // Update select all checkbox
            const totalCheckboxes = $('.category-checkbox').length;
            const checkedCheckboxes = $('.category-checkbox:checked').length;

            $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
            $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
        });

        // Update selected categories array and UI
        function updateSelectedCategories() {
            selectedCategories = [];
            $('.category-checkbox:checked').each(function() {
                selectedCategories.push($(this).val());
            });

            const count = selectedCategories.length;
            $('#selectedCount').text(count);

            if (count > 0) {
                $('#bulkDeleteBtn').show();
            } else {
                $('#bulkDeleteBtn').hide();
            }
        }

        // Bulk delete modal
        $('#bulkDeleteBtn').on('click', function() {
            const count = selectedCategories.length;
            $('#bulkDeleteCount').text(count);

            // Show selected categories list
            let categoriesList = '<strong>Danh mục được chọn:</strong><ul class="mt-2">';
            $('.category-checkbox:checked').each(function() {
                const row = $(this).closest('tr');
                const categoryName = row.find('td:eq(2)').text(); // Cột tên danh mục
                categoriesList += `<li>${categoryName}</li>`;
            });
            categoriesList += '</ul>';
            $('#selectedCategoriesList').html(categoriesList);
        });

        // Handle bulk delete form submission
        $('#bulkDeleteModal form').on('submit', function(e) {
            e.preventDefault();

            // Add selected IDs to form data
            const formData = new FormData(this);
            selectedCategories.forEach(function(id) {
                formData.append('ids[]', id);
            });

            const url = $(this).attr('action');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#bulkDeleteModal .close[data-dismiss="modal"]').trigger('click');
                    categoriesTable.ajax.reload(null, false);

                    // Update statistics
                    updateStats();

                    // Reset selections
                    selectedCategories = [];
                    $('#selectAll').prop('checked', false).prop('indeterminate', false);
                    $('#bulkDeleteBtn').hide();

                    // Show toast
                    const toastType = response.type || 'success';
                    if (typeof AjaxFormHandler !== 'undefined') {
                        AjaxFormHandler.showToast(response.message, toastType);
                    }
                },
                error: function(xhr) {
                    let message = 'Có lỗi xảy ra!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (typeof AjaxFormHandler !== 'undefined') {
                        AjaxFormHandler.showToast(message, 'danger');
                    }
                }
            });
        });
    });
</script>

<!-- Initialize AJAX Form Handler -->
<script>
    $(document).ready(function() {
        if (typeof AjaxFormHandler !== 'undefined') {
            AjaxFormHandler.init({
                table: 'categoriesTable',
                forms: ['#addCategoryModal form', '#editCategoryModal form', '#deleteCategoryModal form'],
                onSuccess: function(response) {
                    // Update statistics after successful operations
                    updateStats();
                }
            });
        }
    });
</script>
@endpush