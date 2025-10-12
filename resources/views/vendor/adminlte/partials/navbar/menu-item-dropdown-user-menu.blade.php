{{-- User Menu --}}
<li class="nav-item dropdown user-menu">
    {{-- Dropdown Toggle --}}
    <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
        <img src="{{ Auth::user()->avatar ? asset(Auth::user()->avatar) : asset('storage/default-avatar.png') }}" 
             class="rounded-circle me-2" alt="Avatar" width="32" height="32" style="object-fit: cover;">
        <span class="d-none d-md-inline fw-semibold mx-2">{{ Auth::user()->name ?? 'User' }}</span>
        <i class="fas fa-caret-down"></i>
    </a>

    {{-- Dropdown Menu --}}
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right user-dropdown shadow-lg border-0">
        {{-- Header --}}
        <div class="dropdown-header text-dark text-center position-relative py-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <img src="{{ Auth::user()->avatar ? asset(Auth::user()->avatar) : asset('storage/default-avatar.png') }}" 
                 class="rounded-circle mb-2 border-white shadow" 
                 width="80" height="80" alt="User Avatar" style="object-fit: cover;">
            <h6 class="mb-0 fw-bold text-white">{{ Auth::user()->name ?? 'User' }}</h6>
            <small class="text-white-50">{{ Auth::user()->email ?? 'user@example.com' }}</small>
            <div class="mt-1">
                @if(Auth::check() && Auth::user())
                <span class="badge bg-{{ Auth::user()->role_id == 1 ? 'danger' : (Auth::user()->role_id == 2 ? 'warning' : 'info') }}">
                    {{ Auth::user()->role_id == 1 ? 'Admin' : (Auth::user()->role_id == 2 ? 'Nhân viên' : 'Khách hàng') }}
                </span>
                @endif
            </div>
        </div>

        {{-- Body --}}
        <div class="dropdown-body py-2">
            <a href="{{ route('admin.profile.index') }}" class="dropdown-item d-flex align-items-center py-1 hover-item">
                <div class="icon-wrapper mr-2">
                    <i class="fas fa-user-circle text-primary"></i>
                </div>
                <div>
                    <div class="fw-semibold">Hồ sơ cá nhân</div>
                    <small class="text-muted">Xem và chỉnh sửa thông tin</small>
                </div>
            </a>
            
            <a href="{{ route('admin.profile.change-password') }}" class="dropdown-item d-flex align-items-center py-1 hover-item">
                <div class="icon-wrapper mr-2">
                    <i class="fas fa-key text-warning"></i>
                </div>
                <div>
                    <div class="fw-semibold">Đổi mật khẩu</div>
                    <small class="text-muted">Cập nhật mật khẩu bảo mật</small>
                </div>
            </a>
            
            <a href="{{ route('admin.profile.settings') }}" class="dropdown-item d-flex align-items-center py-1 hover-item">
                <div class="icon-wrapper mr-2">
                    <i class="fas fa-cogs text-info"></i>
                </div>
                <div>
                    <div class="fw-semibold">Cài đặt</div>
                    <small class="text-muted">Cấu hình hệ thống</small>
                </div>
            </a>
            
            <div class="dropdown-divider"></div>
            
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="dropdown-item d-flex align-items-center py-2 hover-item text-danger">
                    <div class="icon-wrapper mr-2">
                        <i class="fas fa-sign-out-alt text-danger"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">Đăng xuất</div>
                        <small class="text-muted">Thoát khỏi hệ thống</small>
                    </div>
                </button>
            </form>
        </div>
    </div>
</li>

<style>
.user-dropdown {
    min-width: 280px;
    border-radius: 12px;
    overflow: hidden;
}

.user-dropdown .dropdown-header {
    border-radius: 12px 12px 0 0;
}

.hover-item {
    transition: all 0.2s ease;
    border-radius: 8px;
    margin: 2px 8px;
}

.hover-item:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.icon-wrapper {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: rgba(0,0,0,0.05);
    border-radius: 8px;
}

.dropdown-divider {
    margin: 8px 16px;
}

.user-dropdown button.dropdown-item {
    background: none;
    border: none;
    width: 100%;
    text-align: left;
}

.user-dropdown button.dropdown-item:hover {
    background-color: #fff5f5;
}

.user-dropdown .dropdown-body .dropdown-item{
    max-width: 260px;
}
    
</style>
