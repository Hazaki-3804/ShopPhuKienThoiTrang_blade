@extends('layouts.admin')
@section('title', 'Phân quyền chung')

@section('content_header')
<span class="fw-semibold"></span>
@stop

@push('styles')
<style>
    /* Smooth collapse for group bodies */
    .group-card .card-body.collapse {
        display: block;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 250ms ease, opacity 200ms ease;
    }

    .group-card .card-body.collapse.show {
        max-height: 1200px;
        opacity: 1;
    }

    /* Subtle icon animation */
    .btn-toggle i {
        transition: transform 200ms ease;
    }

    .btn-toggle[aria-expanded="true"] i {
        transform: rotate(180deg);
    }
</style>
@endpush

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="m-0"><i class="bi bi-shield-lock-fill"></i> Phân quyền cơ bản cho vai trò: {{ $role->name }}</h4>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left mr-1"></i> Quay lại danh sách
            </a>
        </div>
    </div>
    <div class="card-body">
        @php
        $viLabels = [
        // Sản phẩm
        'view products' => 'Xem sản phẩm',
        'create products' => 'Thêm sản phẩm',
        'edit products' => 'Sửa sản phẩm',
        'delete products' => 'Xóa sản phẩm',

        // Danh mục
        'view categories' => 'Xem danh mục',
        'create categories' => 'Thêm danh mục',
        'edit categories' => 'Sửa danh mục',
        'delete categories' => 'Xóa danh mục',

        // Đơn hàng
        'view orders' => 'Xem đơn hàng',
        'change status orders' => 'Thay đổi trạng thái đơn hàng',
        'print orders' => 'In đơn hàng',
        'view order detail' => 'Xem chi tiết đơn hàng',

        // Khuyến mãi
        'view promotions' => 'Xem khuyến mãi',
        'create promotions' => 'Tạo khuyến mãi',
        'edit promotions' => 'Sửa khuyến mãi',
        'delete promotions' => 'Xóa khuyến mãi',

        // Phí vận chuyển
        'view shipping fees' => 'Xem phí vận chuyển',
        'create shipping fees' => 'Tạo phí vận chuyển',
        'edit shipping fees' => 'Sửa phí vận chuyển',
        'delete shipping fees' => 'Xóa phí vận chuyển',

        // Khách hàng
        'view customers' => 'Xem khách hàng',
        'create customers' => 'Thêm khách hàng',
        'edit customers' => 'Sửa khách hàng',
        'delete customers' => 'Xóa khách hàng',
        'lock/unlock customers' => 'Khóa/Mở khách hàng',

        // Bình luận
        'view reviews' => 'Xem bình luận',
        'hide reviews' => 'Ẩn bình luận',
        'delete reviews' => 'Xóa bình luận',

        // Nhân viên
        'view staffs' => 'Xem nhân viên',
        'create staffs' => 'Thêm nhân viên',
        'edit staffs' => 'Sửa nhân viên',
        'delete staffs' => 'Xóa nhân viên',
        'lock/unlock staffs' => 'Khóa/Mở nhân viên',

        // Thống kê & Hệ thống
        'view reports' => 'Xem báo cáo',
        'manage settings' => 'Cài đặt hệ thống',
        'manage roles' => 'Quản lý vai trò',
        'manage permissions' => 'Phân quyền',
        ];

        $groups = [
        'Sản phẩm' => [
        'view products', 'create products', 'edit products', 'delete products'
        ],
        'Danh mục' => [
        'view categories', 'create categories', 'edit categories', 'delete categories'
        ],
        'Đơn hàng' => [
        'view orders', 'change status orders', 'print orders','view order detail'
        ],
        'Khuyến mãi' => [
        'view promotions', 'create promotions', 'edit promotions', 'delete promotions'
        ],
        'Phí vận chuyển' => [
        'view shipping fees', 'create shipping fees', 'edit shipping fees', 'delete shipping fees'
        ],
        'Khách hàng' => [
        'view customers', 'create customers', 'edit customers', 'delete customers', 'lock/unlock customers'
        ],
        'Bình luận' => [
        'view reviews', 'hide reviews', 'delete reviews'
        ],
        'Nhân viên' => [
        'view staffs', 'create staffs', 'edit staffs', 'delete staffs', 'lock/unlock staffs'
        ],
        'Thống kê' => [
        'view reports'
        ],
        'Hệ thống' => [
        'manage settings', 'manage roles', 'manage permissions'
        ],
        ];
        @endphp
        <form action="{{ route('admin.users.base-permissions.update') }}" method="POST" id="form-base-permissions">
            @csrf
            @method('PUT')

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="bi bi-search"></i> Tìm quyền</label>
                    <input type="text" id="permSearch" class="form-control" placeholder="Nhập từ khóa...">
                </div>
                <div class="col-md-8 d-flex align-items-end mb-1 gap-2">
                    <button type="button" id="btnToggleAll" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-check-square mr-1"></i> <span id="toggleAllLabel">Chọn tất cả</span>
                    </button>
                </div>
            </div>

            <div id="permContainer">
                @foreach($groups as $groupName => $permKeys)
                @php
                switch ($groupName) {
                case 'Sản phẩm': $icon = 'boxes'; $bg = 'bg-primary'; break;
                case 'Danh mục': $icon = 'list-ul'; $bg = 'bg-success'; break;
                case 'Đơn hàng': $icon = 'file-text'; $bg = 'bg-info'; break;
                case 'Khuyến mãi': $icon = 'cash'; $bg = 'bg-warning'; break;
                case 'Phí vận chuyển': $icon = 'truck'; $bg = 'bg-danger'; break;
                case 'Khách hàng': $icon = 'person'; $bg = 'bg-primary'; break;
                case 'Bình luận': $icon = 'chat'; $bg = 'bg-success'; break;
                case 'Nhân viên': $icon = 'person-fill-gear'; $bg = 'bg-info'; break;
                case 'Thống kê': $icon = 'bar-chart-line-fill'; $bg = 'bg-warning'; break;
                case 'Hệ thống': $icon = 'gear'; $bg = 'bg-danger'; break;
                default: $icon = 'stack'; $bg = 'bg-secondary'; break;
                }
                @endphp
                <div class="card mb-3 border-0 shadow-sm group-card" data-group="{{ Str::slug($groupName) }}">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center" style="gap: 1rem">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center" style="min-width: 150px">
                                    <i class="bi bi-{{ $icon }} text-secondary mr-1"></i>
                                    <h6 class="m-0">{{ $groupName }}</h6>
                                </div>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-group-toggle" data-group="{{ Str::slug($groupName) }}">
                                        <i class="fas fa-check-square mr-1"></i> <span class="label">Chọn nhóm</span>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="button" class="btn btn-lg btn-toggle" id="btnToggleGroup-{{ Str::slug($groupName) }}" data-target="#groupBody-{{ Str::slug($groupName) }}" aria-controls="groupBody-{{ Str::slug($groupName) }}" aria-expanded="true">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-3 collapse show" id="groupBody-{{ Str::slug($groupName) }}">
                        <div class="row">
                            @foreach($permKeys as $key)
                            @php
                            $perm = $permissions->firstWhere('name', $key);
                            if (!$perm) continue;
                            $checked = $rolePermissionNames->contains($perm->name);
                            $label = $viLabels[$perm->name] ?? $perm->name;
                            $searchKey = strtolower($perm->name . ' ' . $label . ' ' . $groupName);
                            $groupClass = 'group-'.Str::slug($groupName);
                            @endphp
                            <div class="col-lg-3 col-md-6 col-sm-12 mb-2 perm-item {{ $groupClass }}" data-name="{{ $searchKey }}">
                                <div class="form-check">
                                    <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" id="perm_{{ $perm->id }}" value="{{ $perm->name }}" {{ $checked ? 'checked' : '' }}>
                                    <label class="form-check-label" for="perm_{{ $perm->id }}">{{ $label }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu quyền cơ bản
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra và hiển thị toast từ localStorage
        const toastMessage = localStorage.getItem('toast_message');
        const toastType = localStorage.getItem('toast_type');

        if (toastMessage) {
            localStorage.removeItem('toast_message');
            localStorage.removeItem('toast_type');

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

        // Submit form qua AJAX
        const form = document.getElementById('form-base-permissions');
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        localStorage.setItem('toast_message', data.message || 'Cập nhật quyền cơ bản thành công!');
                        localStorage.setItem('toast_type', 'success');
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Có lỗi xảy ra!');
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi!',
                            html: error.message,
                            timer: 5000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        alert(error.message);
                    }
                });
        });

        const search = document.getElementById('permSearch');
        const container = document.getElementById('permContainer');
        const items = Array.from(container.querySelectorAll('.perm-item'));
        const groupCards = Array.from(container.querySelectorAll('.group-card'));
        search.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            items.forEach(el => {
                const name = el.getAttribute('data-name');
                el.style.display = name.includes(q) ? '' : 'none';
            });
            groupCards.forEach(card => {
                const visible = Array.from(card.querySelectorAll('.perm-item')).some(i => i.style.display !== 'none');
                card.style.display = visible ? '' : 'none';
            });
        });

        const toggleAllBtn = document.getElementById('btnToggleAll');
        const toggleAllLabel = document.getElementById('toggleAllLabel');

        function updateToggleAllLabel() {
            const all = Array.from(document.querySelectorAll('.perm-checkbox'));
            const anyUnchecked = all.some(cb => !cb.checked);
            if (anyUnchecked) {
                toggleAllBtn.classList.remove('btn-outline-secondary');
                toggleAllBtn.classList.add('btn-outline-primary');
                toggleAllLabel.textContent = 'Chọn tất cả';
            } else {
                toggleAllBtn.classList.remove('btn-outline-primary');
                toggleAllBtn.classList.add('btn-outline-secondary');
                toggleAllLabel.textContent = 'Bỏ chọn tất cả';
            }
        }
        updateToggleAllLabel();

        toggleAllBtn.addEventListener('click', function() {
            const checkboxes = Array.from(document.querySelectorAll('.perm-checkbox'));
            const anyUnchecked = checkboxes.some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
            groupCards.forEach(card => updateGroupButtonLabel(card.getAttribute('data-group')));
            updateToggleAllLabel();
        });

        function updateGroupButtonLabel(group) {
            const btn = container.querySelector('.group-card[data-group="' + group + '"] .btn-group-toggle');
            if (!btn) return;
            const anyUnchecked = Array.from(container.querySelectorAll('.group-' + group + ' .perm-checkbox')).some(cb => !cb.checked);
            const labelEl = btn.querySelector('.label');
            if (anyUnchecked) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-outline-primary');
                if (labelEl) labelEl.textContent = 'Chọn nhóm';
            } else {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-outline-secondary');
                if (labelEl) labelEl.textContent = 'Bỏ nhóm';
            }
        }

        const toggles = document.querySelectorAll('.btn-toggle');
        toggles.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetSelector = btn.getAttribute('data-target');
                const target = document.querySelector(targetSelector);
                const isShown = target.classList.contains('show');
                if (isShown) {
                    target.classList.remove('show');
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    target.classList.add('show');
                    btn.setAttribute('aria-expanded', 'true');
                }
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('bi-chevron-down', !isShown);
                    icon.classList.toggle('bi-chevron-up', isShown);
                }
            });
        });

        groupCards.forEach(card => {
            const group = card.getAttribute('data-group');
            updateGroupButtonLabel(group);
        });

        container.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-group-toggle');
            if (!btn) return;
            const group = btn.getAttribute('data-group');
            const checkboxes = container.querySelectorAll('.group-' + group + ' .perm-checkbox');
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
            updateGroupButtonLabel(group);
        });

        function setupDeps(viewName, dependentNames, groupSlug) {
            const viewCb = document.querySelector('input.perm-checkbox[value="' + viewName + '"]');
            const deps = dependentNames.map(v => document.querySelector('input.perm-checkbox[value="' + v + '"]'));

            function enforce(changedCb) {
                if (!viewCb) return;
                const anyOther = deps.some(cb => cb && cb.checked);
                if (anyOther) viewCb.checked = true;
                if (changedCb === viewCb && !viewCb.checked && anyOther) viewCb.checked = true;
                updateToggleAllLabel();
                const card = document.querySelector('.group-card[data-group="' + groupSlug + '"]');
                if (card) updateGroupButtonLabel(groupSlug);
            }
            [viewCb, ...deps].forEach(cb => {
                if (cb) cb.addEventListener('change', function() {
                    enforce(this);
                });
            });
            enforce(null);
        }

        setupDeps('view products', ['create products', 'edit products', 'delete products'], 'san-pham');
        setupDeps('view categories', ['create categories', 'edit categories', 'delete categories'], 'danh-muc');
        setupDeps('view orders', ['change status orders', 'print orders', 'view order detail'], 'don-hang');
        setupDeps('view promotions', ['create promotions', 'edit promotions', 'delete promotions'], 'khuyen-mai');
        setupDeps('view shipping fees', ['create shipping fees', 'edit shipping fees', 'delete shipping fees'], 'phi-van-chuyen');
        setupDeps('view customers', ['edit customers', 'delete customers'], 'khach-hang');
        setupDeps('view reviews', ['hide reviews', 'delete reviews'], 'binh-luan');
        setupDeps('view staffs', ['create staffs', 'edit staffs', 'delete staffs'], 'nhan-vien');
    });
</script>
@endpush