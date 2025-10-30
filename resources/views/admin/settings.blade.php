@extends('layouts.admin')
@section('title', 'Cài đặt hệ thống')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Cài đặt hệ thống</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Cài đặt']]" />
    </div>

    <div class="m-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Có lỗi xảy ra:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Modern Tab Navigation -->
        <div class="settings-tabs-wrapper">
            <ul class="nav nav-tabs-modern" id="settingsTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                        <div class="tab-icon bg-gradient-primary">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="tab-content-text">
                            <span class="tab-title">Thông tin chung</span>
                            <small class="tab-desc">Cấu hình cơ bản</small>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab">
                        <div class="tab-icon bg-gradient-success">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <div class="tab-content-text">
                            <span class="tab-title">Liên hệ</span>
                            <small class="tab-desc">Thông tin liên lạc</small>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="social-tab" data-toggle="tab" href="#social" role="tab">
                        <div class="tab-icon bg-gradient-purple">
                            <i class="fas fa-share-alt"></i>
                        </div>
                        <div class="tab-content-text">
                            <span class="tab-title">Mạng xã hội</span>
                            <small class="tab-desc">Liên kết social</small>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="appearance-tab" data-toggle="tab" href="#appearance" role="tab">
                        <div class="tab-icon bg-gradient-warning">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div class="tab-content-text">
                            <span class="tab-title">Giao diện</span>
                            <small class="tab-desc">Logo & Favicon</small>
                        </div>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="system-tab" data-toggle="tab" href="#system" role="tab">
                        <div class="tab-icon bg-gradient-secondary">
                            <i class="fas fa-server"></i>
                        </div>
                        <div class="tab-content-text">
                            <span class="tab-title">Hệ thống</span>
                            <small class="tab-desc">Cài đặt nâng cao</small>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="settingsTabContent">
            <!-- Tab 1: Thông tin chung -->
            <div class="tab-pane fade show active" id="general" role="tabpanel">
                <form action="{{ route('settings.update') }}" method="POST" class="settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="general">
                    
                    <div class="card modern-card">
                        <div class="card-header bg-gradient-primary text-white">
                            <div class="d-flex align-items-center">
                                <div class="header-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h5 class="mb-0 ml-2">Thông tin chung</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label fw-bold">
                                            <i class="fas fa-store text-primary"></i> Tên website <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('site_name') is-invalid @enderror" 
                                               id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required>
                                        @error('site_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Tên hiển thị của website</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <label for="site_status" class="form-label fw-bold">
                                            <i class="fas fa-toggle-on text-success"></i> Trạng thái website
                                        </label>
                                        <select class="form-select @error('site_status') is-invalid @enderror" id="site_status" name="site_status">
                                            <option value="active" {{ old('site_status', $settings['site_status'] ?? 'active') == 'active' ? 'selected' : '' }}>
                                                <i class="fas fa-check-circle"></i> Hoạt động
                                            </option>
                                            <option value="maintenance" {{ old('site_status', $settings['site_status'] ?? '') == 'maintenance' ? 'selected' : '' }}>
                                                <i class="fas fa-tools"></i> Bảo trì
                                            </option>
                                        </select>
                                        @error('site_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>  
                                   <small class="text-muted">Chọn "Bảo trì" để tạm đóng website</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="site_description" class="form-label fw-bold">
                                    <i class="fas fa-align-left text-secondary"></i> Mô tả website
                                </label>
                                <textarea class="form-control @error('site_description') is-invalid @enderror" 
                                          id="site_description" name="site_description" rows="3" 
                                          placeholder="Nhập mô tả ngắn về website của bạn...">{{ old('site_description', $settings['site_description'] ?? '') }}</textarea>
                                @error('site_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Mô tả này sẽ hiển thị trong meta tags và kết quả tìm kiếm (SEO)</small>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm px-5">
                                    <i class="fas fa-save mr-2"></i> Lưu cài đặt
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 2: Liên hệ -->
            <div class="tab-pane fade" id="contact" role="tabpanel">
                <form action="{{ route('settings.update') }}" method="POST" class="settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="contact">
                    
                    <div class="card modern-card">
                        <div class="card-header bg-gradient-info text-white">
                            <div class="d-flex align-items-center">
                                <div class="header-icon">
                                    <i class="fas fa-address-book"></i>
                                </div>
                                <h5 class="mb-0 ml-2">Thông tin liên hệ</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_phone" class="form-label fw-bold">
                                            <i class="fas fa-phone text-success"></i> Số điện thoại <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('contact_phone') is-invalid @enderror" 
                                               id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" 
                                               placeholder="0779089258" required>
                                        @error('contact_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Số điện thoại hiển thị trên website</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label fw-bold">
                                            <i class="fas fa-envelope text-primary"></i> Email liên hệ <span class="text-danger">*</span>
                                        </label>
                                        <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                               id="contact_email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" 
                                               placeholder="shopnangtho@gmail.com" required>
                                        @error('contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Email nhận thông tin liên hệ từ khách hàng</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="contact_address" class="form-label fw-bold">
                                    <i class="fas fa-map-marker-alt text-danger"></i> Địa chỉ <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('contact_address') is-invalid @enderror" 
                                       id="contact_address" name="contact_address" value="{{ old('contact_address', $settings['contact_address'] ?? '') }}" 
                                       placeholder="Phường Long Châu, TP Vĩnh Long" required>
                                @error('contact_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Địa chỉ cửa hàng/văn phòng</small>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-info bg-gradient-info btn-sm px-5">
                                    <i class="fas fa-save mr-2"></i> Lưu cài đặt
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 3: Mạng xã hội -->
            <div class="tab-pane fade" id="social" role="tabpanel">
                <form action="{{ route('settings.update') }}" method="POST" class="settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="social">
                    
                    <div class="card modern-card">
                        <div class="card-header bg-gradient-purple text-white">
                            <div class="d-flex align-items-center">
                                <div class="header-icon">
                                    <i class="fas fa-share-alt"></i>
                                </div>
                                <h5 class="mb-0 ml-2">Liên kết mạng xã hội</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                <i class="fas fa-info-circle mr-1"></i>
                                Thêm liên kết đến các trang mạng xã hội của bạn. Để trống nếu không sử dụng.
                            </p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_facebook" class="form-label fw-bold">
                                            <i class="fab fa-facebook text-primary"></i> Facebook
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text p-2"><i class="fab fa-facebook"></i></span>
                                            <input type="url" class="form-control @error('contact_facebook') is-invalid @enderror" 
                                                   id="contact_facebook" name="contact_facebook" value="{{ old('contact_facebook', $settings['contact_facebook'] ?? '') }}" 
                                                   placeholder="https://www.facebook.com/yourpage">
                                        </div>
                                        @error('contact_facebook')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_instagram" class="form-label fw-bold">
                                            <i class="fab fa-instagram text-danger"></i> Instagram
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text p-2"><i class="fab fa-instagram"></i></span>
                                            <input type="url" class="form-control @error('contact_instagram') is-invalid @enderror" 
                                                   id="contact_instagram" name="contact_instagram" value="{{ old('contact_instagram', $settings['contact_instagram'] ?? '') }}" 
                                                   placeholder="https://www.instagram.com/yourpage">
                                        </div>
                                        @error('contact_instagram')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_youtube" class="form-label fw-bold">
                                            <i class="fab fa-youtube text-danger"></i> YouTube
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text p-2"><i class="fab fa-youtube"></i></span>
                                            <input type="url" class="form-control @error('contact_youtube') is-invalid @enderror" 
                                                   id="contact_youtube" name="contact_youtube" value="{{ old('contact_youtube', $settings['contact_youtube'] ?? '') }}" 
                                                   placeholder="https://www.youtube.com/yourchannel">
                                        </div>
                                        @error('contact_youtube')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_tiktok" class="form-label fw-bold">
                                            <i class="fab fa-tiktok"></i> TikTok
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text p-2"><i class="fab fa-tiktok"></i></span>
                                            <input type="url" class="form-control @error('contact_tiktok') is-invalid @enderror" 
                                                   id="contact_tiktok" name="contact_tiktok" value="{{ old('contact_tiktok', $settings['contact_tiktok'] ?? '') }}" 
                                                   placeholder="https://www.tiktok.com/@yourpage">
                                        </div>
                                        @error('contact_tiktok')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary bg-gradient-purple btn-sm px-5">
                                    <i class="fas fa-save mr-2"></i> Lưu cài đặt
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 4: Giao diện -->
            <div class="tab-pane fade" id="appearance" role="tabpanel">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" class="settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="appearance">
                    
                    <div class="card modern-card">
                        <div class="card-header bg-gradient-warning text-dark">
                            <div class="d-flex align-items-center">
                                <div class="header-icon">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <h5 class="mb-0 ml-2">Cài đặt giao diện</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Logo Section -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3"><i class="fas fa-image mr-2"></i>Logo Website</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="custom-file mb-3">
                                            <input type="file" class="custom-file-input" id="logo_file" name="logo_file" accept="image/*">
                                            <label class="custom-file-label" for="logo_file">Chọn file logo...</label>
                                        </div>
                                        <input type="hidden" name="site_logo" id="site_logo" value="{{ old('site_logo', $settings['site_logo'] ?? '') }}">
                                        <small class="text-muted d-block">
                                            <i class="fas fa-info-circle"></i> Định dạng: JPG, PNG, SVG. Kích thước đề xuất: 200x100px
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="upload-preview-box" id="logo-preview-box">
                                            @if(!empty($settings['site_logo']))
                                                <img src="{{ asset($settings['site_logo']) }}" alt="Logo" class="preview-image" id="logo-preview">
                                                <button type="button" class="btn btn-sm btn-danger remove-image" data-target="logo" title="Xóa ảnh">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <div class="preview-placeholder" id="logo-placeholder">
                                                    <i class="fas fa-image fa-3x text-muted"></i>
                                                    <p class="text-muted mt-2 mb-0">Chưa có logo</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Favicon Section -->
                            <div class="mb-4">
                                <h6 class="text-warning mb-3"><i class="fas fa-star mr-2"></i>Favicon</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="custom-file mb-3">
                                            <input type="file" class="custom-file-input" id="favicon_file" name="favicon_file" accept="image/*,.ico">
                                            <label class="custom-file-label" for="favicon_file">Chọn file favicon...</label>
                                        </div>
                                        <input type="hidden" name="site_favicon" id="site_favicon" value="{{ old('site_favicon', $settings['site_favicon'] ?? '') }}">
                                        <small class="text-muted d-block">
                                            <i class="fas fa-info-circle"></i> Định dạng: ICO, PNG. Kích thước đề xuất: 32x32px hoặc 64x64px
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="upload-preview-box favicon-box" id="favicon-preview-box">
                                            @if(!empty($settings['site_favicon']))
                                                <img src="{{ asset($settings['site_favicon']) }}" alt="Favicon" class="preview-image" id="favicon-preview">
                                                <button type="button" class="btn btn-sm btn-danger remove-image" data-target="favicon" title="Xóa ảnh">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @else
                                                <div class="preview-placeholder" id="favicon-placeholder">
                                                    <i class="fas fa-star fa-3x text-muted"></i>
                                                    <p class="text-muted mt-2 mb-0">Chưa có favicon</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-lightbulb mr-2"></i>
                                <strong>Lưu ý:</strong> Ảnh sẽ được lưu vào thư mục <code>public/img/</code>. Ảnh cũ sẽ tự động bị xóa khi upload ảnh mới.
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-warning  bg-gradient-warning btn-sm px-5">
                                    <i class="fas fa-save mr-2"></i> Lưu cài đặt
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tab 5: Hệ thống -->
            <div class="tab-pane fade" id="system" role="tabpanel">
                <form action="{{ route('settings.update') }}" method="POST" class="settings-form">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="system">
                    
                    <div class="card modern-card">
                        <div class="card-header bg-gradient-secondary text-white">
                            <div class="d-flex align-items-center">
                                <div class="header-icon">
                                    <i class="fas fa-server"></i>
                                </div>
                                <h5 class="mb-0 ml-2">Cài đặt hệ thống</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Cảnh báo:</strong> Các cài đặt này ảnh hưởng trực tiếp đến hoạt động của website. Vui lòng cân nhắc kỹ trước khi thay đổi.
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card border-info mb-3">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <h6 class="card-title mb-1">
                                                        <i class="fas fa-tools text-warning mr-1"></i>Chế độ bảo trì
                                                    </h6>
                                                    <p class="card-text text-muted small mb-0">
                                                        Khi bật, website sẽ hiển thị trang bảo trì cho tất cả người dùng (trừ admin). 
                                                        Sử dụng khi cần nâng cấp hoặc sửa chữa website.
                                                    </p>
                                                </div>
                                                <div class="form-check form-switch ms-3 d-flex align-items-center">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="maintenance_switch" 
                                                           {{ old('site_status', $settings['site_status'] ?? 'active') == 'maintenance' ? 'checked' : '' }}
                                                           onchange="document.getElementById('site_status').value = this.checked ? 'maintenance' : 'active'">
                                                    <label class="form-check-label" for="maintenance_switch"></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-secondary">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle mr-1"></i>Thông tin hệ thống</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Laravel Version:</strong> {{ app()->version() }}</p>
                                            <p class="mb-2"><strong>PHP Version:</strong> {{ phpversion() }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2"><strong>Environment:</strong> {{ config('app.env') }}</p>
                                            <p class="mb-2"><strong>Debug Mode:</strong> {{ config('app.debug') ? 'Enabled' : 'Disabled' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-secondary btn-sm px-5">
                                    <i class="fas fa-save mr-2"></i> Lưu cài đặt
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ========================================
   MODERN SETTINGS TAB DESIGN
   ======================================== */

/* Tab Wrapper */
.settings-tabs-wrapper {
    background: #ffffff;
    border-radius: 16px;
    padding: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
}

/* Modern Tab Navigation */
.nav-tabs-modern {
    border: none;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.nav-tabs-modern .nav-item {
    flex: 1;
    min-width: 180px;
}

.nav-tabs-modern .nav-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border: 2px solid transparent;
    border-radius: 12px;
    background: #f8f9fa;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.nav-tabs-modern .nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nav-tabs-modern .nav-link:hover::before {
    opacity: 1;
}

.nav-tabs-modern .nav-link:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.nav-tabs-modern .nav-link.active {
    background: #ffffff;
    border-color: #e0e0e0;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
    transform: translateY(-3px);
}

/* Tab Icon */
.tab-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: #ffffff;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.nav-tabs-modern .nav-link:hover .tab-icon {
    transform: scale(1.1) rotate(5deg);
}

.nav-tabs-modern .nav-link.active .tab-icon {
    transform: scale(1.15);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Tab Content Text */
.tab-content-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
    text-align: left;
}

.tab-title {
    font-weight: 600;
    font-size: 0.95rem;
    color: #2c3e50;
    transition: color 0.3s ease;
}

.tab-desc {
    font-size: 0.75rem;
    color: #6c757d;
    transition: color 0.3s ease;
}

.nav-tabs-modern .nav-link.active .tab-title {
    color: #1a1a1a;
}

.nav-tabs-modern .nav-link.active .tab-desc {
    color: #495057;
}

/* Gradient Backgrounds */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-purple {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
}

/* ========================================
   MODERN CARDS
   ======================================== */
.modern-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    margin-bottom: 2rem;
    overflow: hidden;
    background: #ffffff;
}

.modern-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    transform: translateY(-2px);
}

.modern-card .card-header {
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 600;
    position: relative;
    overflow: hidden;
}

.modern-card .card-header::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    transform: translate(50%, -50%);
}

.header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.modern-card .card-body {
    padding: 2rem;
}

/* ========================================
   FORM CONTROLS
   ======================================== */
.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
    transform: translateY(-1px);
}

.form-label {
    margin-bottom: 0.75rem;
    color: #2c3e50;
    font-weight: 600;
    font-size: 0.9rem;
}

.form-label i {
    width: 22px;
    text-align: center;
    margin-right: 4px;
}

/* Input Groups */
.input-group-text {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #e9ecef;
    border-right: none;
    border-radius: 10px 0 0 10px;
    font-weight: 500;
    padding: 0.75rem 1rem;
}

.input-group .form-control {
    border-left: none;
    border-radius: 0 10px 10px 0;
}

.input-group .form-control:focus {
    border-left: 2px solid #667eea;
}

/* Switch */
.form-check-input {
    width: 3.5rem;
    height: 1.75rem;
    cursor: pointer;
    border-radius: 2rem;
    transition: all 0.3s ease;
}

.form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

/* ========================================
   ALERTS
   ======================================== */
.alert {
    border-left: 4px solid;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.alert-success {
    border-left-color: #38ef7d;
    background: linear-gradient(135deg, rgba(56, 239, 125, 0.1) 0%, rgba(17, 153, 142, 0.05) 100%);
}

.alert-danger {
    border-left-color: #f5576c;
    background: linear-gradient(135deg, rgba(213, 59, 79, 0.1) 0%, rgba(240, 147, 251, 0.05) 100%);
}

.alert-warning {
    border-left-color: #fee140;
    background: linear-gradient(135deg, rgba(254, 225, 64, 0.15) 0%, rgba(250, 112, 154, 0.08) 100%);
}

.alert-info {
    border-left-color: #667eea;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%);
}

/* ========================================
   BUTTONS
   ======================================== */
.btn {
    border-radius: 10px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
}
#contact_facebook{
    padding: 10px 0;
}

/* ========================================
   ANIMATIONS
   ======================================== */
.tab-pane {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ========================================
   UPLOAD PREVIEW
   ======================================== */
.upload-preview-box {
    position: relative;
    width: 100%;
    height: 160px;
    border: 3px dashed #e0e0e0;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    overflow: hidden;
    transition: all 0.4s ease;
}

.upload-preview-box:hover {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f0ff 0%, #ffffff 100%);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.15);
}

.upload-preview-box.favicon-box {
    height: 130px;
}

.preview-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.preview-placeholder {
    text-align: center;
    transition: all 0.3s ease;
}

.upload-preview-box:hover .preview-placeholder i {
    transform: scale(1.1);
    color: #667eea;
}

.remove-image {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.3s ease;
    background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
    border: 2px solid #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.upload-preview-box:hover .remove-image {
    opacity: 1;
}

.remove-image:hover {
    transform: scale(1.1) rotate(90deg);
}

/* ========================================
   RESPONSIVE DESIGN
   ======================================== */
@media (max-width: 992px) {
    .nav-tabs-modern .nav-item {
        min-width: 150px;
    }
    
    .nav-tabs-modern .nav-link {
        padding: 12px 16px;
        gap: 10px;
    }
    
    .tab-icon {
        width: 42px;
        height: 42px;
        font-size: 1.1rem;
    }
    
    .tab-title {
        font-size: 0.9rem;
    }
    
    .tab-desc {
        font-size: 0.7rem;
    }
    
    .modern-card .card-body {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .settings-tabs-wrapper {
        padding: 6px;
    }
    
    .nav-tabs-modern {
        gap: 6px;
    }
    
    .nav-tabs-modern .nav-item {
        min-width: 100%;
        flex: none;
    }
    
    .nav-tabs-modern .nav-link {
        padding: 14px 16px;
    }
    
    .tab-content-text {
        flex: 1;
    }
    
    
    .modern-card .card-body {
        padding: 1.25rem;
    }
    
    .btn {
        padding: 0.65rem 1.5rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .tab-desc {
        display: none;
    }
    
    .header-icon {
        width: 36px;
        height: 36px;
        font-size: 1.1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Bootstrap 4 tab initialization
    $('#settingsTabs a[data-toggle="tab"]').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    
    // Auto-save tab state
    $('#settingsTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeSettingsTab', $(e.target).attr('href'));
    });
    
    // Restore last active tab
    var activeTab = localStorage.getItem('activeSettingsTab');
    if (activeTab) {
        $('#settingsTabs a[href="' + activeTab + '"]').tab('show');
    }
    
    // Confirm before leaving if form is dirty
    var formChanged = false;
    $('.settings-form input, .settings-form textarea, .settings-form select').on('change', function() {
        formChanged = true;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'Bạn có thay đổi chưa được lưu. Bạn có chắc muốn rời khỏi trang?';
        }
    });
    
    $('.settings-form').on('submit', function() {
        formChanged = false;
    });
    
    // ============================================
    // IMAGE UPLOAD HANDLING
    // ============================================
    
    // Update custom file input label (Bootstrap 4)
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass('selected').html(fileName);
    });
    
    // Logo upload preview
    $('#logo_file').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Vui lòng chọn file ảnh!');
                $(this).val('');
                return;
            }
            
            // Validate file size (max 2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 2MB!');
                $(this).val('');
                return;
            }
            
            // Preview image
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#logo-placeholder').hide();
                $('#logo-preview').remove();
                $('.remove-image[data-target="logo"]').remove();
                
                var img = $('<img>', {
                    src: e.target.result,
                    alt: 'Logo',
                    class: 'preview-image',
                    id: 'logo-preview'
                });
                
                var removeBtn = $('<button>', {
                    type: 'button',
                    class: 'btn btn-sm btn-danger remove-image',
                    'data-target': 'logo',
                    title: 'Xóa ảnh',
                    html: '<i class="fas fa-times"></i>'
                });
                
                $('#logo-preview-box').append(img).append(removeBtn);
            };
            reader.readAsDataURL(file);
            formChanged = true;
        }
    });
    
    // Favicon upload preview
    $('#favicon_file').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            // Validate file type
            if (!file.type.match('image.*') && !file.name.endsWith('.ico')) {
                alert('Vui lòng chọn file ảnh hoặc .ico!');
                $(this).val('');
                return;
            }
            
            // Validate file size (max 1MB)
            if (file.size > 1 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 1MB!');
                $(this).val('');
                return;
            }
            
            // Preview image
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#favicon-placeholder').hide();
                $('#favicon-preview').remove();
                $('.remove-image[data-target="favicon"]').remove();
                
                var img = $('<img>', {
                    src: e.target.result,
                    alt: 'Favicon',
                    class: 'preview-image',
                    id: 'favicon-preview'
                });
                
                var removeBtn = $('<button>', {
                    type: 'button',
                    class: 'btn btn-sm btn-danger remove-image',
                    'data-target': 'favicon',
                    title: 'Xóa ảnh',
                    html: '<i class="fas fa-times"></i>'
                });
                
                $('#favicon-preview-box').append(img).append(removeBtn);
            };
            reader.readAsDataURL(file);
            formChanged = true;
        }
    });
    
    // Remove image button
    $(document).on('click', '.remove-image', function() {
        var target = $(this).data('target');
        
        if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
            if (target === 'logo') {
                $('#logo_file').val('');
                $('.custom-file-label[for="logo_file"]').html('Chọn file logo...');
                $('#logo-preview').remove();
                $(this).remove();
                $('#logo-placeholder').show();
                $('#site_logo').val('');
            } else if (target === 'favicon') {
                $('#favicon_file').val('');
                $('.custom-file-label[for="favicon_file"]').html('Chọn file favicon...');
                $('#favicon-preview').remove();
                $(this).remove();
                $('#favicon-placeholder').show();
                $('#site_favicon').val('');
            }
            formChanged = true;
        }
    });
});
</script>
@endpush
