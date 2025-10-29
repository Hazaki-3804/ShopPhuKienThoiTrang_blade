@extends('layouts.admin')
@section('title', 'Thêm chương trình khuyến mãi')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3 mb-3">
        <h4 class="fw-semibold m-0">Thêm chương trình khuyến mãi mới</h4>
        <x-admin.breadcrumbs :items="[
            ['name' => 'Trang chủ'], 
            ['name' => 'Quản lý khuyến mãi', 'url' => route('admin.promotions.index')], 
            ['name' => 'Thêm mới']
        ]" />
    </div>

    <div class="px-3">
        <form id="promotionForm" action="{{ route('admin.promotions.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin chương trình</h5>
                        </div>
                        <div class="card-body">
                            <!-- Code -->
                            <div class="form-group">
                                <label for="code">Mã khuyến mãi <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" 
                                    placeholder="VD: GIAM50K, SALE30" required>
                                <small class="form-text text-muted">Mã sẽ tự động chuyển thành chữ in hoa</small>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Mô tả</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Mô tả chi tiết về chương trình khuyến mãi"></textarea>
                            </div>

                            <!-- Discount Type and Value -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount_type">Loại giảm giá <span class="text-danger">*</span></label>
                                        <select class="form-control" id="discount_type" name="discount_type" required>
                                            <option value="percent">Phần trăm (%)</option>
                                            <option value="amount">Số tiền cố định (₫)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount_value">Giá trị giảm <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                            min="0" step="0.01" placeholder="VD: 10 hoặc 50000" required>
                                        <small class="form-text text-muted" id="discount-hint">
                                            Nhập giá trị từ 0-100 cho phần trăm
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1">Kích hoạt</option>
                                    <option value="0">Tạm dừng</option>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="form-group">
                                <label for="quantity">Số lượng Voucher</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                    value="" min="0" step="1" 
                                    placeholder="Để trống nếu không giới hạn">
                                <small class="form-text text-muted">
                                    Số lượng voucher có thể sử dụng trong khoảng thời gian này. Để trống nếu không giới hạn.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-success">
                            <h5 class="mb-0"><i class="fas fa-box"></i> Sản phẩm áp dụng</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Chọn sản phẩm</label>
                                <div class="mb-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllProducts">
                                        <i class="fas fa-check-double mr-1"></i> Chọn tất cả
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllProducts">
                                        <i class="fas fa-times mr-1"></i> Bỏ chọn tất cả
                                    </button>
                                </div>
                                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    @forelse($products as $product)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                name="products[]" value="{{ $product->id }}" 
                                                id="product_{{ $product->id }}">
                                            <label class="form-check-label" for="product_{{ $product->id }}">
                                                {{ $product->name }}
                                                <small class="text-muted d-block">
                                                    {{ number_format($product->price, 0, ',', '.') }}₫
                                                </small>
                                            </label>
                                        </div>
                                    @empty
                                        <p class="text-muted text-center">Không có sản phẩm nào</p>
                                    @endforelse
                                </div>
                                <small class="form-text text-muted mt-2">
                                    <i class="fas fa-info-circle"></i> Đã chọn: <strong id="selectedProductCount">0</strong> sản phẩm
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-save"></i> Lưu chương trình
                            </button>
                            <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Update discount hint based on type
    $('#discount_type').on('change', function() {
        const type = $(this).val();
        const hint = $('#discount-hint');
        const input = $('#discount_value');
        
        if (type === 'percent') {
            hint.text('Nhập giá trị từ 0-100 cho phần trăm');
            input.attr('max', 100);
        } else {
            hint.text('Nhập số tiền giảm giá (VD: 50000 cho 50k)');
            input.removeAttr('max');
        }
    });

    // Select/Deselect all products
    $('#selectAllProducts').on('click', function() {
        $('.product-checkbox').prop('checked', true);
        updateProductCount();
    });

    $('#deselectAllProducts').on('click', function() {
        $('.product-checkbox').prop('checked', false);
        updateProductCount();
    });

    // Update product count
    $('.product-checkbox').on('change', function() {
        updateProductCount();
    });

    function updateProductCount() {
        const count = $('.product-checkbox:checked').length;
        $('#selectedProductCount').text(count);
    }

    // Form submission
    $('#promotionForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable submit button
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang xử lý...');
        
        $.ajax({
            url: form.attr('action'),
            method: form.attr('method'),
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showToast('success', response.message);
                    
                    // Redirect after 1 second
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    for (let field in errors) {
                        errorMessage += errors[field][0] + '<br>';
                    }
                    showToast('danger', errorMessage);
                } else {
                    const message = xhr.responseJSON?.message || 'Có lỗi xảy ra!';
                    showToast('danger', message);
                }
            }
        });
    });

    function showToast(type, message) {
        const iconMap = {
            'success': 'success',
            'danger': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        const titleMap = {
            'success': 'Thành công!',
            'danger': 'Lỗi!',
            'warning': 'Cảnh báo!',
            'info': 'Thông tin!'
        };
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: iconMap[type] || 'success',
                title: titleMap[type] || 'Thành công!',
                html: message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        } else {
            alert(message);
        }
    }

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    $('#start_date').attr('min', today);
    // Get today for input
    $('#start_date').val(today);
    
    // Update end date minimum when start date changes
    $('#start_date').on('change', function() {
        $('#end_date').attr('min', $(this).val());
    });
});
</script>
@endpush
