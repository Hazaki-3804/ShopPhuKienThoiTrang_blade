@extends('layouts.admin')
@section('title', 'Quản lý sản phẩm')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-4">
        <h4 class="fw-semibold m-0">Quản lý sản phẩm</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý sản phẩm']]" />
    </div>

    <!-- Statistics Cards -->
    <div class="row mx-3 my-3">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['total_products'] }}</h3>
                            <p class="mb-0 small">Tổng sản phẩm</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-cubes fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['active_products'] }}</h3>
                            <p class="mb-0 small">Đang bán</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['inactive_products'] }}</h3>
                            <p class="mb-0 small">Tạm dừng</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-pause-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['low_stock_products'] }}</h3>
                            <p class="mb-0 small">Sắp hết hàng</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['out_of_stock'] }}</h3>
                            <p class="mb-0 small">Hết hàng</p>
                        </div>
                        <div class="text-right">
                            <i class="fas fa-times-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $stats['recent_products'] }}</h3>
                            <p class="mb-0 small">Mới trong tuần</p>
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
        <!-- Left side - Search and Filters -->
            <div class="flex-grow-1">
                <input type="search" id="productSearch"
                    class="form-control form-control-sm me-2"
                    placeholder="Tìm kiếm sản phẩm..."
                    style="max-width: 200px;">
            
            </div>

            <!-- Right side - Bulk actions, Add button and Export -->
            <div class="d-flex align-items-center">
            
                <select id="categoryFilter" class="form-control form-control-sm mr-2" style="max-width: 170px;">
                    <option value="">-- Tất cả danh mục --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            
                <select id="statusFilter" class="form-control form-control-sm mr-2" style="max-width: 170px;">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1">Đang bán</option>
                    <option value="0">Tạm dừng</option>
                </select>
            
            <!-- Bulk delete button (hidden by default) -->
                @if(auth()->user()->can('delete products'))
                <button type="button" class="btn btn-danger btn-sm mr-2" id="bulkDeleteBtn" style="display: none;" data-toggle="modal" data-target="#bulkDeleteModal">
                    <i class="fas fa-trash"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
                </button>
                @endif
                
                @if(auth()->user()->can('create products'))
                <a href="{{ route('admin.products.create') }}" class="btn btn-success btn-sm mr-2" title="Thêm sản phẩm">
                    <i class="fas fa-plus mr-1"></i> Thêm sản phẩm mới
                </a>
                @endif

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
                <table class="table table-bordered table-striped align-middle w-100" id="productsTable">
                    <thead class="table-info">
                        <tr>
                            <th width="30" style="padding:0; text-align:center; vertical-align:middle;">
                                <div style="display:flex; justify-content:center; align-items:center; height:100%;">
                                    <input type="checkbox" id="selectAll" class="form-check-input" style="margin:0;">
                                </div>
                            </th>
                            <th width="50px">STT</th>
                            <th>Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá (VNĐ)</th>
                            <th>Tồn kho</th>
                            <th>Trạng thái</th>
                            <th width="120px">Thao tác</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@include('admin.products.product-detail')

<!-- Add Product Modal -->
<x-admin.modal
    modal_id='addProductModal'
    title="Thêm sản phẩm mới"
    url="{{ route('admin.products.store') }}"
    button_type="save"
    method="POST">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="add_name" class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_name" name="name" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="add_category_id" class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                <select class="form-control" id="add_category_id" name="category_id" required>
                    <option value="">-- Chọn danh mục --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="add_price" class="form-label fw-bold">Giá <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="add_price" name="price" min="0" step="1000" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="add_stock" class="form-label fw-bold">Tồn kho <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="add_stock" name="stock" min="0" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="add_status" class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                <select class="form-control" id="add_status" name="status" required>
                    <option value="1">Đang bán</option>
                    <option value="0">Tạm dừng</option>
                </select>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label for="add_description" class="form-label fw-bold">Mô tả</label>
        <textarea class="form-control" id="add_description" name="description" rows="3" placeholder="Mô tả sản phẩm..."></textarea>
    </div>
</x-admin.modal>

<!-- Edit Product Modal -->
<x-admin.modal
    modal_id='editProductModal'
    title="Cập nhật sản phẩm"
    url="{{ route('admin.products.update') }}"
    button_type="update"
    method="PUT">
    <input type="hidden" name="id" id="edit_product_id">
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="edit_name" class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_name" name="name" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label for="edit_category_id" class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                <select class="form-control" id="edit_category_id" name="category_id" required>
                    <option value="">-- Chọn danh mục --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="edit_price" class="form-label fw-bold">Giá <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="edit_price" name="price" min="0" step="1000" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="edit_stock" class="form-label fw-bold">Tồn kho <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="edit_stock" name="stock" min="0" required>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label for="edit_status" class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                <select class="form-control" id="edit_status" name="status" required>
                    <option value="1">Đang bán</option>
                    <option value="0">Tạm dừng</option>
                </select>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <label for="edit_description" class="form-label fw-bold">Mô tả</label>
        <textarea class="form-control" id="edit_description" name="description" rows="3" placeholder="Mô tả sản phẩm..."></textarea>
    </div>
</x-admin.modal>

<!-- Delete Product Modal -->
<x-admin.modal
    modal_id='deleteProductModal'
    title="Xóa sản phẩm"
    url="{{ route('admin.products.destroy') }}"
    button_type="delete"
    method="DELETE">
    <div class="mb-3">
        <input type="hidden" name="id" id="del_product_id">
        <p class="fw-bold">Bạn có chắc chắn muốn xóa sản phẩm "<span id="del_product_name" class="text-danger"></span>"?</p>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Lưu ý:</strong> Không thể xóa sản phẩm đã có trong giỏ hàng hoặc đơn hàng. Dữ liệu sẽ không thể khôi phục.
        </div>
    </div>
</x-admin.modal>

<!-- Bulk Delete Modal -->
<x-admin.modal
    modal_id='bulkDeleteModal'
    title="Xóa nhiều sản phẩm"
    url="{{ route('admin.products.destroy.multiple') }}"
    button_type="delete"
    method="DELETE">
    <div class="mb-3">
        <p class="fw-bold">Bạn có chắc chắn muốn xóa <span id="bulkDeleteCount" class="text-danger"></span> sản phẩm đã chọn?</p>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Lưu ý:</strong> Không thể xóa sản phẩm đã có trong giỏ hàng hoặc đơn hàng. Dữ liệu sẽ không thể khôi phục.
        </div>
        <div id="selectedProductsList" class="mt-2">
            <!-- Danh sách các sản phẩm được chọn sẽ hiển thị ở đây -->
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
        window.productsTable = $('#productsTable').DataTable({
            processing: false,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.products.data') }}",
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
                    data: 'product_info',
                    name: 'name'
                },
                {
                    data: 'category_name',
                    name: 'category.name'
                },
                {
                    data: 'price_formatted',
                    name: 'price',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'stock_badge',
                    name: 'stock',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'status_badge',
                    name: 'status',
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

        // Search functionality
        let typingTimer;
        const typingDelay = 500;

        $('#productSearch').on('keyup', function() {
            clearTimeout(typingTimer);
            const value = this.value;

            typingTimer = setTimeout(function() {
                productsTable.search(value).draw();
            }, typingDelay);
        });

        // Filter functionality
        $('#categoryFilter, #statusFilter').on('change', function() {
            productsTable.ajax.reload();
        });

        // Export buttons
        $('#btn-excel').on('click', function(e) {
            e.preventDefault();
            productsTable.button('.buttons-excel').trigger();
        });
        $('#btn-csv').on('click', function(e) {
            e.preventDefault();
            productsTable.button('.buttons-csv').trigger();
        });
        $('#btn-pdf').on('click', function(e) {
            e.preventDefault();
            productsTable.button('.buttons-pdf').trigger();
        });
        $('#btn-print').on('click', function(e) {
            e.preventDefault();
            productsTable.button('.buttons-print').trigger();
        });

        // Modal handlers
        $(document).on('click', '.view-product', function() {
            let productId = $(this).data('id');
            let productName = $(this).data('name');
            let productDescription = $(this).data('description');
            let category = $(this).data('category');
            let price = $(this).data('price');
            let stock = $(this).data('stock');
            let status = $(this).data('status');
            let created = $(this).data('created');
            let image = $(this).data('image');
            
            $('#view_id').text(productId);
            $('#view_name').text(productName);
            $('#view_category').text(category);
            $('#view_price').text(price + ' VNĐ');
            $('#view_stock').text(stock + ' sản phẩm');
            $('#view_status').text(status);
            $('#view_created').text(created);
            $('#view_image').attr('src', image);
            
            if (productDescription && productDescription.trim() !== '') {
                productDescription = productDescription.replace(/\.\s*/g, '.<br>');
                $('#view_description').html(productDescription);
            } else {
                $('#view_description').html('<em class="text-muted">Chưa có mô tả</em>');
            }
            
            // Store data for edit button
            $('#editFromView').data({
                'id': productId,
                'name': productName,
                'description': productDescription,
                'category-id': $(this).data('category-id') || '',
                'price': $(this).data('price-raw') || price.replace(/[^\d]/g, ''),
                'stock': stock.replace(/[^\d]/g, ''),
                'status': status === 'Đang bán' ? '1' : '0'
            });
        });

        // Edit from view modal - redirect to edit page
        $('#editFromView').on('click', function() {
            let productId = $(this).data('id');
            if (productId) {
                window.location.href = '{{ url("/admin/products") }}/' + productId + '/edit';
            }
        });

        $(document).on('click', '.edit-product', function() {
            let productId = $(this).data('id');
            let productName = $(this).data('name');
            let productDescription = $(this).data('description');
            let categoryId = $(this).data('category-id');
            let price = $(this).data('price');
            let stock = $(this).data('stock');
            let status = $(this).data('status');
            
            $('#edit_product_id').val(productId);
            $('#edit_name').val(productName);
            $('#edit_description').val(productDescription || '');
            $('#edit_category_id').val(categoryId);
            $('#edit_price').val(price);
            $('#edit_stock').val(stock);
            $('#edit_status').val(status);
        });

        $(document).on('click', '.delete-product', function() {
            let productId = $(this).data('id');
            let productName = $(this).data('name');
            
            $('#del_product_id').val(productId);
            $('#del_product_name').text(productName);
        });



        // Checkbox functionality
        let selectedProducts = [];

        // Select all checkbox
        $('#selectAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.product-checkbox').prop('checked', isChecked);
            updateSelectedProducts();
        });

        // Individual checkbox
        $(document).on('change', '.product-checkbox', function() {
            updateSelectedProducts();
            
            // Update select all checkbox
            const totalCheckboxes = $('.product-checkbox').length;
            const checkedCheckboxes = $('.product-checkbox:checked').length;
            
            $('#selectAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
            $('#selectAll').prop('checked', checkedCheckboxes === totalCheckboxes);
        });

        // Update selected products array and UI
        function updateSelectedProducts() {
            selectedProducts = [];
            $('.product-checkbox:checked').each(function() {
                selectedProducts.push($(this).val());
            });
            
            const count = selectedProducts.length;
            $('#selectedCount').text(count);
            
            if (count > 0) {
                $('#bulkDeleteBtn').show();
            } else {
                $('#bulkDeleteBtn').hide();
            }
        }

        // Bulk delete modal
        $('#bulkDeleteBtn').on('click', function() {
            const count = selectedProducts.length;
            $('#bulkDeleteCount').text(count);
            
            // Show selected products list
            let productsList = '<strong>Sản phẩm được chọn:</strong><ul class="mt-2">';
            $('.product-checkbox:checked').each(function() {
                const row = $(this).closest('tr');
                const productName = row.find('td:eq(2)').text(); // Cột tên sản phẩm
                productsList += `<li>${productName}</li>`;
            });
            productsList += '</ul>';
            $('#selectedProductsList').html(productsList);
        });

        // Function to update statistics (make it global)
        window.updateProductStats = function() {
            $.ajax({
                url: '{{ route("admin.products.stats") }}',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const stats = response.stats;
                        
                        // Update each statistic card with animation
                        $('.card.bg-primary .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.total_products).fadeIn(200);
                        });
                        $('.card.bg-success .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.active_products).fadeIn(200);
                        });
                        $('.card.bg-secondary .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.inactive_products).fadeIn(200);
                        });
                        $('.card.bg-warning .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.low_stock_products).fadeIn(200);
                        });
                        $('.card.bg-danger .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.out_of_stock).fadeIn(200);
                        });
                        $('.card.bg-info .card-body h3').fadeOut(200, function() {
                            $(this).text(stats.recent_products).fadeIn(200);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error updating product stats:', xhr);
                }
            });
        }
        updateProductStats(); // gọi khi vừa load trang

        // Handle bulk delete form submission
        $('#bulkDeleteModal form').on('submit', function(e) {
            e.preventDefault();
            
            // Add selected IDs to form data
            const formData = new FormData(this);
            selectedProducts.forEach(function(id) {
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
                    productsTable.ajax.reload(null, false);
                    
                    // Update statistics
                    updateProductStats();
                    
                    // Reset selections
                    selectedProducts = [];
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
    // Kiểm tra và hiển thị toast từ localStorage
    const toastMessage = localStorage.getItem('toast_message');
    const toastType = localStorage.getItem('toast_type');
    
    if (toastMessage) {
        // Xóa khỏi localStorage
        localStorage.removeItem('toast_message');
        localStorage.removeItem('toast_type');
        
        // Hiển thị toast
        if (typeof Swal !== 'undefined') {
            const iconMap = {
                'success': 'success',
                'error': 'error',
                'warning': 'warning',
                'info': 'info'
            };
            
            const titleMap = {
                'success': 'Thành công!',
                'error': 'Lỗi!',
                'warning': 'Cảnh báo!',
                'info': 'Thông tin!'
            };
            
            Swal.fire({
                icon: iconMap[toastType] || 'success',
                title: titleMap[toastType] || 'Thành công!',
                html: toastMessage,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    }
    
    if (typeof AjaxFormHandler !== 'undefined') {
        AjaxFormHandler.init({
            table: 'productsTable',
            forms: ['#addProductModal form', '#editProductModal form', '#deleteProductModal form'],
            onSuccess: function(response) {
                // Update statistics after successful operations
                updateProductStats();
            }
        });
    }
});
</script>
@endpush