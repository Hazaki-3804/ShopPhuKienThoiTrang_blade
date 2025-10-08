@extends('layouts.admin')
@section('title', 'Cài đặt hệ thống')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header giống card-header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Cài đặt hệ thống</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Hồ sơ cá nhân', 'url' => route('admin.profile.index')], ['name' => 'Cài đặt']]" />
    </div>

    <div class="m-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs text-primary"></i> Cài đặt chung
                </h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.profile.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="site_name" class="form-label fw-bold">
                                    <i class="fas fa-store text-primary"></i> Tên website <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                       id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required>
                                @error('site_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_email" class="form-label fw-bold">
                                    <i class="fas fa-envelope text-info"></i> Email liên hệ <span class="text-danger">*</span>
                                </label>
                                <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                       id="contact_email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" required>
                                @error('contact_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label fw-bold">
                                    <i class="fas fa-phone text-success"></i> Số điện thoại
                                </label>
                                <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                       id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}">
                                @error('contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contact_address" class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt text-danger"></i> Địa chỉ
                                </label>
                                <input type="text" class="form-control @error('contact_address') is-invalid @enderror" 
                                       id="contact_address" name="contact_address" value="{{ old('contact_address', $settings['contact_address']) }}">
                                @error('contact_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="site_description" class="form-label fw-bold">
                            <i class="fas fa-align-left text-secondary"></i> Mô tả website
                        </label>
                        <textarea class="form-control @error('site_description') is-invalid @enderror" 
                                  id="site_description" name="site_description" rows="3">{{ old('site_description', $settings['site_description']) }}</textarea>
                        @error('site_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" 
                                       {{ old('maintenance_mode', $settings['maintenance_mode']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="maintenance_mode">
                                    <i class="fas fa-tools text-warning"></i> Chế độ bảo trì
                                </label>
                                <small class="text-muted d-block">Kích hoạt để tạm thời đóng website</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration" value="1" 
                                       {{ old('allow_registration', $settings['allow_registration']) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="allow_registration">
                                    <i class="fas fa-user-plus text-success"></i> Cho phép đăng ký
                                </label>
                                <small class="text-muted d-block">Cho phép khách hàng tự đăng ký tài khoản</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Lưu cài đặt
                        </button>
                    </div>
                </form>
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
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush
