@extends('layouts.admin')
@section('title', 'Chỉnh sửa chương trình khuyến mãi')
@section('content_header')
<span class="fw-semibold"></span>
@stop
@section('content')
<div class="shadow-sm rounded bg-white py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3 mb-3">
        <h4 class="fw-semibold m-0">Chỉnh sửa chương trình khuyến mãi</h4>
        <x-admin.breadcrumbs :items="[
            ['name' => 'Trang chủ'], 
            ['name' => 'Quản lý khuyến mãi', 'url' => route('admin.promotions.index')], 
            ['name' => 'Chỉnh sửa']
        ]" />
    </div>

    <div class="px-3">
        <form id="promotionForm" action="{{ route('admin.promotions.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $promotion->id }}">
            
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
                                    value="{{ $promotion->code }}" placeholder="VD: GIAM50K, SALE30" required>
                                <small class="form-text text-muted">Mã sẽ tự động chuyển thành chữ in hoa</small>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Mô tả</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Mô tả chi tiết về chương trình khuyến mãi">{{ $promotion->description }}</textarea>
                            </div>

                            <!-- Discount Type and Value -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount_type">Loại giảm giá <span class="text-danger">*</span></label>
                                        <select class="form-control" id="discount_type" name="discount_type" required>
                                            <option value="percent" {{ $promotion->discount_type === 'percent' ? 'selected' : '' }}>Phần trăm (%)</option>
                                            <option value="amount" {{ $promotion->discount_type === 'amount' ? 'selected' : '' }}>Số tiền cố định (₫)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount_value">Giá trị giảm <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="discount_value" name="discount_value" 
                                            value="{{ $promotion->discount_value }}" min="0" step="0.01" 
                                            placeholder="VD: 10 hoặc 50000" required>
                                        <small class="form-text text-muted" id="discount-hint">
                                            {{ $promotion->discount_type === 'percent' ? 'Nhập giá trị từ 0-100 cho phần trăm' : 'Nhập số tiền giảm giá (VD: 50000 cho 50k)' }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Date Range -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date">Ngày bắt đầu <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" 
                                            value="{{ $promotion->start_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">Ngày kết thúc <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" 
                                            value="{{ $promotion->end_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label for="status">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1" {{ $promotion->status == 1 ? 'selected' : '' }}>Kích hoạt</option>
                                    <option value="0" {{ $promotion->status == 0 ? 'selected' : '' }}>Tạm dừng</option>
                                </select>
                            </div>

                            <!-- Quantity -->
                            <div class="form-group">
                                <label for="quantity">Số lượng Voucher</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                    value="{{ $promotion->quantity }}" min="0" step="1" 
                                    placeholder="Để trống nếu không giới hạn">
                                <small class="form-text text-muted">
                                    Số lượng voucher có thể sử dụng trong khoảng thời gian này. Để trống nếu không giới hạn.
                                    @if($promotion->used_quantity > 0)
                                        <br><strong>Đã sử dụng: {{ $promotion->used_quantity }}</strong>
                                    @endif
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
                                        <i class="fas fa-check-double"></i> Chọn tất cả
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllProducts">
                                        <i class="fas fa-times"></i> Bỏ chọn tất cả
                                    </button>
                                </div>
                                <div style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    @forelse($products as $product)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input product-checkbox" type="checkbox" 
                                                name="products[]" value="{{ $product->id }}" 
                                                id="product_{{ $product->id }}"
                                                {{ in_array($product->id, $selectedProducts) ? 'checked' : '' }}>
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
                                    <i class="fas fa-info-circle"></i> Đã chọn: <strong id="selectedProductCount">{{ count($selectedProducts) }}</strong> sản phẩm
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-save"></i> Cập nhật chương trình
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
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message using AjaxFormHandler
                    if (typeof AjaxFormHandler !== 'undefined') {
                        AjaxFormHandler.showToast(response.message, 'success');
                    }
                    
                    // Redirect after 1.5 seconds
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1500);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                
                let message = 'Có lỗi xảy ra!';
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    const errorList = [];
                    for (let field in errors) {
                        errorList.push(errors[field][0]);
                    }
                    message = errorList.join('<br>');
                } else if (xhr.responseJSON?.message) {
                    message = xhr.responseJSON.message;
                }
                
                if (typeof AjaxFormHandler !== 'undefined') {
                    AjaxFormHandler.showToast(message, 'danger');
                }
            }
        });
    });

    // Update end date minimum when start date changes
    $('#start_date').on('change', function() {
        $('#end_date').attr('min', $(this).val());
    });
});
</script>
@endpush
