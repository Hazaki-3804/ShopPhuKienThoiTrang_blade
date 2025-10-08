@extends('layouts.app')
@section('title', 'Thông tin tài khoản')
@push('styles')
<style>
  /* Wrapper */
  .profile-page { background: linear-gradient(180deg, #fff 0%, #fff4f1 100%); }
  .profile-card { border: none; border-radius: 16px; }
  .profile-sidebar { border-right: 1px solid #f1f1f1; }
  /* Avatar */
  .profile-avatar { box-shadow: 0 8px 20px rgba(238,77,45,.2); }
  /* Tabs */
  .profile-tabs.nav-tabs { border-bottom: 1px solid #ffe0d9; }
  .profile-tabs .nav-link { color: #6c757d; font-weight: 600; border: none; padding: .6rem 1rem; }
  .profile-tabs .nav-link:hover { color: #EE4D2D; background: #fff3ef; border-radius: 10px; }
  .profile-tabs .nav-link.active { color: #EE4D2D; background: #ffede7; border-radius: 10px; }
  /* Cards */
  .profile-section { border-radius: 12px; border: 1px solid #f3f3f3; }
  .profile-section h6 { color: #333; }
  /* Order list */
  .order-item { border: 1px solid #f4f4f4; border-radius: 12px; }
  .order-item:hover { box-shadow: 0 6px 16px rgba(0,0,0,.06); }
  .order-status.badge { background: #ffede7; color: #EE4D2D; font-weight: 600; }
  /* Buttons (brand) */
  .btn-brand { background: #EE4D2D; color: #fff; border-color: #EE4D2D; }
  .btn-brand:hover { background: #d94527; border-color: #d94527; color: #fff; }
  .btn-outline-brand { color: #EE4D2D; border-color: #EE4D2D; }
  .btn-outline-brand:hover { background: #ffede7; color: #EE4D2D; }
  /* Active/clicked state turns orange */
  .btn-outline-brand.active,
  .btn-outline-brand:active,
  .btn-outline-brand:focus {
    background: #EE4D2D !important;
    color: #fff !important;
    border-color: #EE4D2D !important;
    box-shadow: none !important;
  }
  /* Subtle press effect */
  .profile-action-btn { transition: transform .08s ease, box-shadow .2s ease; }
  .profile-action-btn:active { transform: translateY(1px) scale(0.99); }
  /* Ensure equal height and centered content */
  .profile-actions .btn { min-height: 38px; display: inline-flex; align-items: center; }

  /* Highlight pulse on settings panel */
  @keyframes brandPulse {
    0% { box-shadow: 0 0 0 0 rgba(238,77,45, .45); }
    100% { box-shadow: 0 0 0 12px rgba(238,77,45, 0); }
  }
  .highlight-pulse { animation: brandPulse .8s ease-out 1; border-color: #EE4D2D !important; }
</style>
@endpush
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 profile-page">
            <div class="card shadow profile-card rounded-4 p-4">
                <div class="row">
                    <!-- Left: Avatar + Name + Stats -->
                    <div class="col-md-4 text-center profile-sidebar">
                        <style>
                            .avatar-wrap {
                                position: relative;
                                display: inline-block;
                            }

                            .avatar-edit {
                                position: absolute;
                                right: 0;
                                bottom: 8px;
                                background: rgba(0, 0, 0, .6);
                                color: #fff;
                                border: none;
                                width: 36px;
                                height: 36px;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                cursor: pointer;
                                opacity: 0;
                                transition: opacity .2s;
                            }

                            .avatar-wrap:hover .avatar-edit {
                                opacity: 1;
                            }
                        </style>
                        <div class="avatar-wrap mb-1">
                            <img src="{{ $user->avatar }}"
                                class="rounded-circle profile-avatar"
                                width="130" height="130" alt="Avatar">
                            <button type="button" class="avatar-edit" id="btnEditAvatar" title="Đổi avatar">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </div>
                        @if ($errors->has('avatar'))
                        <div class="text-danger small mb-2">{{ $errors->first('avatar') }}</div>
                        @endif
                        <form id="avatarForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="d-none">
                            @csrf
                            @method('PUT')
                            <input type="file" name="avatar" id="avatarInput" accept="image/*">
                        </form>
                        <h4 class="fw-bold">{{ $user->name }}</h4>
                        <div class="profile-actions d-flex justify-content-center align-items-center gap-2 mb-2">
                            <button type="button" class="btn btn-outline-brand btn-sm px-3 py-2 profile-action-btn" data-bs-toggle="tab" data-bs-target="#settings">
                                <i class="bi bi-pencil-square me-1"></i> Cập nhật info
                            </button>
                            <button type="button" class="btn btn-outline-brand btn-sm px-3 py-2 profile-action-btn" data-bs-toggle="tab" data-bs-target="#password">
                                <i class="bi bi-key me-1"></i> Đổi mật khẩu
                            </button>
                        </div>

                        <!-- Stats đơn hàng -->
                        <div class="row text-center mt-4">
                            <div class="col-4">
                                <h5 class="mb-0">{{ $user->orders_count ?? 0 }}</h5>
                                <small class="text-muted">Đơn hàng</small>
                            </div>
                            <div class="col-4">
                                <h5 class="mb-0">{{ $user->wishlist_count ?? 0 }}</h5>
                                <small class="text-muted">Yêu thích</small>
                            </div>
                            <div class="col-4">
                                <h5 class="mb-0">{{ $user->points ?? 0 }}</h5>
                                <small class="text-muted">Điểm thưởng</small>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Tabs -->
                    <div class="col-md-8 mt-4 mt-md-0">
                        <ul class="nav nav-tabs profile-tabs mb-3" id="profileTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="info-tab" data-bs-toggle="tab"
                                    data-bs-target="#info" type="button" role="tab">Thông tin</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="settings-tab" data-bs-toggle="tab"
                                    data-bs-target="#settings" type="button" role="tab">Cài đặt</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="password-tab" data-bs-toggle="tab"
                                    data-bs-target="#password" type="button" role="tab">Đổi mật khẩu</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="wishlist-tab" data-bs-toggle="tab"
                                    data-bs-target="#wishlist" type="button" role="tab">Yêu thích</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="profileTabContent">
                            <!-- Tab Thông tin -->
                            <div class="tab-pane fade show active" id="info" role="tabpanel">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th>Email:</th>
                                            <td>{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>Số điện thoại:</th>
                                            <td>{{ $user->phone ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Địa chỉ giao hàng:</th>
                                            <td>{{ $user->address ?? '-' }}</td>
                                    </tbody>
                                </table>
                            </div>


                            <!-- Tab Cài đặt -->
                            <div class="tab-pane fade" id="settings" role="tabpanel">
                                <div class="row g-4">
                                    <!-- Form cập nhật thông tin -->
                                    <div class="col-12">
                                        <div class="card shadow-sm p-3">
                                            <h6 class="fw-bold mb-3">Cập nhật thông tin</h6>
                                            @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @endif
                                            <form action="{{ route('profile.update') }}" method="POST" class="row g-3" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-md-6">
                                                    <label class="form-label">Username</label>
                                                    <input type="text" name="username" class="form-control" value="{{ old('username', $user->username) }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Họ và tên</label>
                                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Số điện thoại</label>
                                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Địa chỉ</label>
                                                    <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Avatar (tùy chọn)</label>
                                                    <input type="file" name="avatar" class="form-control" accept="image/*">
                                                </div>
                                                <div class="col-12">
                                                    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu thay đổi</button>
                                                    <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">Hủy</a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- Tab Đổi mật khẩu riêng -->
                            <div class="tab-pane fade" id="password" role="tabpanel">
                                <div class="card shadow-sm p-3">
                                    <h6 class="fw-bold mb-3">Đổi mật khẩu</h6>
                                    <form method="POST" action="{{ route('profile.password.update') }}" class="row g-3">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-12">
                                            <label class="form-label">Mật khẩu hiện tại</label>
                                            <input type="password" name="current_password" class="form-control" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Mật khẩu mới</label>
                                            <input type="password" name="password" class="form-control" minlength="8" required>
                                            <small class="text-muted">Tối thiểu 8 ký tự</small>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Xác nhận mật khẩu mới</label>
                                            <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-primary"><i class="bi bi-shield-lock me-1"></i> Cập nhật mật khẩu</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Tab Wishlist -->
                            <div class="tab-pane fade" id="wishlist" role="tabpanel">
                            </div>
                        </div> <!-- End Right Tabs -->
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const editBtn = document.getElementById('btnEditAvatar');
                                const fileInput = document.getElementById('avatarInput');
                                const avatarForm = document.getElementById('avatarForm');
                                if (editBtn && fileInput && avatarForm) {
                                    editBtn.addEventListener('click', function() {
                                        fileInput.click();
                                    });
                                    fileInput.addEventListener('change', function() {
                                        if (fileInput.files.length) avatarForm.submit();
                                    });
                                }

                                // Ensure left buttons open Settings tab
                                const settingsTrigger = document.getElementById('settings-tab');
                                const passwordTrigger = document.getElementById('password-tab');
                                const leftButtonsSettings = document.querySelectorAll('button[data-bs-target="#settings"]');
                                leftButtonsSettings.forEach(btn => {
                                    btn.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        if (settingsTrigger) {
                                            const tab = new bootstrap.Tab(settingsTrigger);
                                            tab.show();
                                        }
                                    });
                                });
                                const leftButtonsPassword = document.querySelectorAll('button[data-bs-target="#password"]');
                                leftButtonsPassword.forEach(btn => {
                                    btn.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        if (passwordTrigger) {
                                            const tab = new bootstrap.Tab(passwordTrigger);
                                            tab.show();
                                        }
                                    });
                                });

                                // Toggle orange active background for action buttons
                                const actionBtns = document.querySelectorAll('.profile-action-btn');
                                function setActiveButton(target){
                                    actionBtns.forEach(b => b.classList.remove('active'));
                                    if (target) target.classList.add('active');
                                }
                                actionBtns.forEach(btn => {
                                    btn.addEventListener('click', function(){ setActiveButton(this); });
                                });
                                // Default active on first button
                                if (actionBtns.length) setActiveButton(actionBtns[0]);

                                // Auto-open Settings tab after validation errors or flash
                                const hasAnyErrors = @json($errors->any());
                                const passwordErrors = @json($errors->has('current_password') || $errors->has('password'));
                                const successMsg = @json(session('success'));
                                const errorMsg = @json(session('error'));
                                if (passwordErrors) {
                                    if (passwordTrigger) {
                                        const tab = new bootstrap.Tab(passwordTrigger);
                                        tab.show();
                                    }
                                } else if (hasAnyErrors || successMsg || errorMsg) {
                                    if (settingsTrigger) {
                                        const tab = new bootstrap.Tab(settingsTrigger);
                                        tab.show();
                                    }
                                }
                            });
                        </script>
                    </div> <!-- End Row -->
                </div> <!-- End Card -->
            </div>
        </div>
    </div>
    @endsection