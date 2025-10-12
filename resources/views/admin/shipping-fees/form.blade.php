@php
    $prefix = isset($isEdit) && $isEdit ? 'edit-' : '';
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}name" class="form-label">Tên quy tắc <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="{{ $prefix }}name" name="name" required>
    </div>

    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}area_type" class="form-label">Loại khu vực <span class="text-danger">*</span></label>
        <select class="form-control" id="{{ $prefix }}area_type" name="area_type" required>
            <option value="local">Nội thành Vĩnh Long</option>
            <option value="nearby">Lân cận</option>
            <option value="nationwide">Toàn quốc</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}min_distance" class="form-label">Khoảng cách tối thiểu (km) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="{{ $prefix }}min_distance" name="min_distance" step="0.01" min="0" value="0" required>
    </div>

    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}max_distance" class="form-label">Khoảng cách tối đa (km)</label>
        <input type="number" class="form-control" id="{{ $prefix }}max_distance" name="max_distance" step="0.01" min="0" placeholder="Để trống = không giới hạn">
        <small class="form-text text-muted">Để trống nếu không giới hạn khoảng cách</small>
    </div>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}min_order_value" class="form-label">Giá trị đơn hàng tối thiểu (₫) <span class="text-danger">*</span></label>
    <input type="number" class="form-control" id="{{ $prefix }}min_order_value" name="min_order_value" step="1000" min="0" value="0" required>
</div>

<div class="mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="{{ $prefix }}is_free_shipping" name="is_free_shipping" value="1">
        <label class="form-check-label" for="{{ $prefix }}is_free_shipping">
            <strong>Miễn phí vận chuyển</strong>
        </label>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="{{ $prefix }}base_fee" class="form-label">Phí cơ bản (₫) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="{{ $prefix }}base_fee" name="base_fee" step="1000" min="0" value="30000" required>
    </div>

    <div class="col-md-4 mb-3">
        <label for="{{ $prefix }}per_km_fee" class="form-label">Phí mỗi km (₫) <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="{{ $prefix }}per_km_fee" name="per_km_fee" step="100" min="0" value="0" required>
    </div>

    <div class="col-md-4 mb-3">
        <label for="{{ $prefix }}max_fee" class="form-label">Phí tối đa (₫)</label>
        <input type="number" class="form-control" id="{{ $prefix }}max_fee" name="max_fee" step="1000" min="0" placeholder="Để trống = không giới hạn">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}priority" class="form-label">Độ ưu tiên <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="{{ $prefix }}priority" name="priority" min="0" value="0" required>
        <small class="form-text text-muted">Số càng cao càng được ưu tiên áp dụng trước</small>
    </div>

    <div class="col-md-6 mb-3">
        <label for="{{ $prefix }}status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
        <select class="form-control" id="{{ $prefix }}status" name="status" required>
            <option value="1">Kích hoạt</option>
            <option value="0">Tắt</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label for="{{ $prefix }}description" class="form-label">Mô tả</label>
    <textarea class="form-control" id="{{ $prefix }}description" name="description" rows="3" placeholder="Mô tả chi tiết về quy tắc phí vận chuyển"></textarea>
</div>
