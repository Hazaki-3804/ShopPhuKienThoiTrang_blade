@extends('layouts.admin')
@section('title', 'Đổi mật khẩu')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Đổi mật khẩu</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Hồ sơ cá nhân', 'url' => route('admin.profile.index')], ['name' => 'Đổi mật khẩu']]" />
    </div>

    <div class="row justify-content-center m-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-key text-warning"></i> Đổi mật khẩu
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i> <strong>Lưu ý: </strong>Hệ thống sẽ tự động đăng xuất bạn sau khi đổi mật khẩu để bảo mật tài khoản.
                    </div>
            
                    <form action="{{ route('admin.profile.change-password.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-bold">
                                <i class="fas fa-lock text-secondary"></i> Mật khẩu hiện tại <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password" required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold">
                                <i class="fas fa-key text-primary"></i> Mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required minlength="8">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Mật khẩu phải có ít nhất 8 ký tự</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label fw-bold">
                                <i class="fas fa-key text-success"></i> Xác nhận mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required minlength="8">
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list text-success"></i> Quy tắt đặt mật khẩu mới
                    </h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li class="list-group-item">✅ Độ dài tối thiểu 8 ký tự</li>
                        <li class="list-group-item">✅ Phải chứa ít nhất 1 chữ hoa</li>
                        <li class="list-group-item">✅ Phải chứa ít nhất 1 chữ thường</li>
                        <li class="list-group-item">✅ Phải chứa ít nhất 1 số</li>
                        <li class="list-group-item">✅ Phải chứa ít nhất 1 ký tự đặc biệt</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Kiểm tra mật khẩu xác nhận
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();
        
        if (password !== confirmPassword) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Mật khẩu xác nhận không khớp</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
});
</script>
@endpush
