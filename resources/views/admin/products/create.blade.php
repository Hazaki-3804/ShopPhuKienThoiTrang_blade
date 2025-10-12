@extends('layouts.admin')
@section('title', 'Thêm sản phẩm mới')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3 mb-3">
        <h4 class="fw-semibold m-0">Thêm sản phẩm mới</h4>
        <x-admin.breadcrumbs :items="[
            ['name' => 'Trang chủ'], 
            ['name' => 'Quản lý sản phẩm', 'url' => route('admin.products.index')], 
            ['name' => 'Thêm sản phẩm']
        ]" />
    </div>

    <div class="card m-3">
        <div class="card-header">
            <h5 class="mb-0">
                Thông tin sản phẩm
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
                @csrf
                
                <div class="row">
                    <!-- Left Column - Product Info -->
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">
                                        Tên sản phẩm <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label fw-bold">
                                        Danh mục <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label fw-bold">
                                        Giá bán <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                               id="price" name="price" value="{{ old('price') }}" min="0" step="1000" required>
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="stock" class="form-label fw-bold">
                                        Số lượng <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                           id="stock" name="stock" value="{{ old('stock') }}" min="10" required>
                                    @error('stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">
                                        Trạng thái <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Đang bán</option>
                                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Tạm dừng</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">
                                Mô tả sản phẩm
                            </label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Nhập mô tả chi tiết về sản phẩm...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Right Column - Image Upload -->
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Hình ảnh sản phẩm
                            </label>
                            
                            <!-- Simple file input as fallback -->
                            <div class="mb-2" style="display: none;">
                                <input type="file" class="form-control" id="simpleImageInput" accept="image/*" multiple>
                                <small class="text-muted">Chọn nhiều hình ảnh (JPG, PNG, GIF - tối đa 5MB mỗi file)</small>
                            </div>
                            
                            <!-- Dropzone area -->
                            <div class="dropzone-area" id="productImageDropzone">
                                <div class="dz-message">
                                    <div class="text-center">
                                        <h5 class="text-muted">Kéo thả hình ảnh vào đây</h5>
                                        <p class="text-muted mb-0">hoặc <strong>click để chọn file</strong></p>
                                        <small class="text-muted">Hỗ trợ: JPG, PNG, GIF (tối đa 5MB)</small>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="uploaded_images" id="uploadedImages">
                        </div>

                        <!-- Preview Images -->
                        <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                            <label class="form-label fw-bold">
                                Xem trước
                            </label>
                            <div id="imagePreviewList" class="row g-2">
                                <!-- Preview images will be added here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Quay lại
                            </a>
                            <div>
                                <button type="reset" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-undo me-1"></i> Đặt lại
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Lưu sản phẩm
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<style>
.dropzone-area {
    border: 2px dashed #0087F7;
    border-radius: 10px;
    background: #f8f9fa;
    min-height: 150px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dropzone-area:hover {
    border-color: #0056b3;
    background: #e3f2fd;
}

.dropzone-area.dz-drag-hover {
    border-color: #28a745;
    background: #d4edda;
}

/* Override default dropzone styles */
.dropzone .dz-message {
    margin: 0 !important;
}

.dropzone .dz-preview {
    display: none !important;
}

.preview-image {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.preview-image img {
    width: 100%;
    height: 80px;
    object-fit: cover;
}

.remove-image {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(220, 53, 69, 0.8);
    color: white;
    border: none;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    font-size: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.remove-image:hover {
    background: rgba(220, 53, 69, 1);
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let uploadedFiles = [];
    
    // Theo dõi trạng thái form đã thay đổi
    let formChanged = false;
    let uploadedTempImages = [];
    
    // Đánh dấu form đã thay đổi khi user nhập liệu
    $('#productForm input, #productForm select, #productForm textarea').on('change input', function() {
        formChanged = true;
        localStorage.setItem('product_form_changed', 'true');
    });
    
    // Cảnh báo khi rời trang nếu form đã thay đổi
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            const message = 'Bạn có chắc chắn muốn rời trang? Tất cả dữ liệu đã nhập sẽ bị mất!';
            e.preventDefault();
            e.returnValue = message;
            return message;
        }
    });
    
    // Xóa cảnh báo khi submit form thành công
    $('#productForm').on('submit', function() {
        formChanged = false;
        localStorage.removeItem('product_form_changed');
        window.removeEventListener('beforeunload', arguments.callee);
    });
    
    // Kiểm tra và xóa ảnh tạm khi trang load
    if (localStorage.getItem('product_form_changed') === 'true') {
        // Xóa ảnh tạm từ session trước đó
        $.ajax({
            url: '{{ route("admin.products.clear-temp-images") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Đã xóa ảnh tạm từ session trước');
            }
        });
        localStorage.removeItem('product_form_changed');
    }
    
    // Simple file input handler
    $('#simpleImageInput').on('change', function(e) {
        const files = e.target.files;
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                uploadFile(files[i]);
            }
        }
    });
    
    // Drag and drop handlers
    const dropzone = document.getElementById('productImageDropzone');
    
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('dz-drag-hover');
    });
    
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dz-drag-hover');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dz-drag-hover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            for (let i = 0; i < files.length; i++) {
                uploadFile(files[i]);
            }
        }
    });
    
    // Click to select files
    dropzone.addEventListener('click', function() {
        $('#simpleImageInput').click();
    });
    
    function uploadFile(file) {
        // Validate file
        if (!file.type.startsWith('image/')) {
            alert('Chỉ chấp nhận file hình ảnh!');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) { // 5MB
            alert('File quá lớn! Tối đa 5MB.');
            return;
        }
        
        const formData = new FormData();
        formData.append('image', file);
        
        // Debug URL - with fallback
        let uploadUrl = '{{ route("admin.products.upload-image") }}';
        
        // Fallback URL if route doesn't work
        if (!uploadUrl || uploadUrl.includes('route')) {
            uploadUrl = '/admin/products/upload-image';
        }
        
        console.log('Upload URL:', uploadUrl);
        
        // Show loading
        const loadingHtml = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang upload...</div>';
        $('#imagePreviewContainer').show();
        $('#imagePreviewList').append('<div class="col-12 loading-item">' + loadingHtml + '</div>');
        
        $.ajax({
            url: uploadUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('.loading-item').remove();
                
                if (response.success) {
                    uploadedFiles.push(response.filename);
                    uploadedTempImages.push(response.filename); // Theo dõi ảnh tạm
                    formChanged = true; // Đánh dấu form đã thay đổi
                    localStorage.setItem('product_form_changed', 'true');
                    updateUploadedImages();
                    addImagePreview(response.filename, response.url);
                } else {
                    alert(response.message || 'Upload thất bại!');
                }
            },
            error: function(xhr) {
                $('.loading-item').remove();
                
                let message = 'Có lỗi xảy ra khi upload!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
            }
        });
    }
    
    function updateUploadedImages() {
        $('#uploadedImages').val(JSON.stringify(uploadedFiles));
    }
    
    function addImagePreview(filename, url) {
        const previewHtml = `
            <div class="col-6 preview-image" data-filename="${filename}">
                <img src="${url}" alt="Preview" class="img-fluid">
                <button type="button" class="remove-image" onclick="removeImage('${filename}')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        $('#imagePreviewList').append(previewHtml);
        $('#imagePreviewContainer').show();
    }
    
    // Global function to remove image
    window.removeImage = function(filename) {
        // Xóa ảnh tạm từ server
        $.ajax({
            url: '{{ route("admin.products.clear-temp-images") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                filename: filename
            },
            success: function(response) {
                console.log('Đã xóa ảnh tạm:', filename);
            },
            error: function() {
                console.log('Lỗi khi xóa ảnh tạm:', filename);
            }
        });
        
        uploadedFiles = uploadedFiles.filter(f => f !== filename);
        uploadedTempImages = uploadedTempImages.filter(f => f !== filename);
        updateUploadedImages();
        $(`.preview-image[data-filename="${filename}"]`).remove();
        
        if (uploadedFiles.length === 0) {
            $('#imagePreviewContainer').hide();
        }
    };
    
    // Form validation
    $('#productForm').on('submit', function(e) {
        const name = $('#name').val().trim();
        const price = $('#price').val();
        const stock = $('#stock').val();
        const categoryId = $('#category_id').val();
        
        if (!name || !price || !stock || !categoryId) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
            return false;
        }
        
        if (parseFloat(price) < 0) {
            e.preventDefault();
            alert('Giá sản phẩm phải lớn hơn 0!');
            return false;
        }
        
        if (parseInt(stock) < 0) {
            e.preventDefault();
            alert('Số lượng tồn kho phải lớn hơn hoặc bằng 0!');
            return false;
        }
        
        // Xóa cảnh báo khi submit thành công
        formChanged = false;
        localStorage.removeItem('product_form_changed');
    });
    
    // Reset form
    $('button[type="reset"]').on('click', function() {
        // Cleanup temp images trước khi reset
        if (uploadedFiles.length > 0) {
            cleanupTempImages(uploadedFiles);
        }
        
        uploadedFiles = [];
        uploadedTempImages = [];
        formChanged = false;
        localStorage.removeItem('product_form_changed');
        updateUploadedImages();
        $('#imagePreviewList').empty();
        $('#imagePreviewContainer').hide();
        $('#simpleImageInput').val('');
    });
    
    // Cleanup khi rời khỏi trang mà chưa lưu
    // $(window).on('beforeunload', function() {
    //     if (uploadedFiles.length > 0) {
    //         cleanupTempImages(uploadedFiles);
    //     }
    // });
    $(window).on('beforeunload', function() {
        if (uploadedFiles.length > 0) {
            const url = '/admin/products/clear-temp-images';
            const data = JSON.stringify({ files: uploadedFiles });
            navigator.sendBeacon(url, data);
        }
    });

    
    // Function để cleanup temp images
    // function cleanupTempImages(filenames) {
    //     if (!filenames || filenames.length === 0) return;
        
    //     $.ajax({
    //         url: '{{ route("admin.products.clear-temp-images") }}',
    //         type: 'POST',
    //         data: {
    //             filenames: filenames,
    //             _token: $('meta[name="csrf-token"]').attr('content')
    //         },
    //         async: false, // Đồng bộ để đảm bảo cleanup trước khi rời trang
    //         success: function(response) {
    //             console.log('Temp images cleaned up:', response.message);
    //         },
    //         error: function(xhr) {
    //             console.error('Failed to cleanup temp images:', xhr.responseJSON?.message);
    //         }
    //     });
    // }
});
</script>
@endpush