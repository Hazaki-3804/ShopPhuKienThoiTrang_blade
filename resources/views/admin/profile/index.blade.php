@extends('layouts.admin')
@section('title', 'Hồ sơ cá nhân')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Hồ sơ cá nhân</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Hồ sơ cá nhân']]" />
    </div>

    <div class="row m-3">
        <!-- Profile Info Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=random&color=fff&size=40' }}" 
                             alt="Avatar" 
                             class="rounded-circle border-primary shadow" 
                             width="120" height="120"
                             style="object-fit: cover;">
                    </div>
                    <h5 class="fw-bold">{{ $user->username }}</h5>
                    <p class="text-muted">{{ $user->email }}</p>
                    <span class="badge bg-{{ $user->role_id == 1 ? 'danger' : ($user->role_id == 2 ? 'warning' : 'info') }}">
                        {{ $user->role_id == 1 ? 'Admin' : ($user->role_id == 2 ? 'Nhân viên' : 'Khách hàng') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit text-primary"></i> Cập nhật thông tin
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">
                                        <i class="fas fa-user text-primary"></i> Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">
                                        <i class="fas fa-envelope text-info"></i> Email <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-bold">
                                        <i class="fas fa-phone text-success"></i> Số điện thoại
                                    </label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="avatar" class="form-label fw-bold">
                                        <i class="fas fa-image text-warning"></i> Avatar
                                    </label>
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                           id="avatar" name="avatar" accept="image/*">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt text-danger"></i> Địa chỉ
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{route('dashboard')}}" class='btn btn-secondary'><i class='fas fa-arrow-left'></i> Quay lại trang chủ</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Cập nhật thông tin
                            </button>
                        </div>
                    </form>
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
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Đang cập nhật...');
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Lưu message vào localStorage
                localStorage.setItem('toast_message', response.message || 'Cập nhật thông tin thành công!');
                localStorage.setItem('toast_type', 'success');
                
                // Reload trang để cập nhật avatar
                window.location.reload();
            },
            error: function(xhr) {
                $submitBtn.prop('disabled', false).html(originalText);
                
                let message = 'Có lỗi xảy ra!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                
                // Hiển thị validation errors
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errorHtml = '<ul class="mb-0 text-start">';
                    Object.values(xhr.responseJSON.errors).forEach(function(errors) {
                        errors.forEach(function(error) {
                            errorHtml += '<li>' + error + '</li>';
                        });
                    });
                    errorHtml += '</ul>';
                    message = errorHtml;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        html: message,
                        timer: 5000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    alert(message);
                }
            }
        });
    });
});
</script>
@endpush
