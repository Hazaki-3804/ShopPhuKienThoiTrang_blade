@extends('layouts.admin')
@section('title', 'Import Danh Mục')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="container-fluid px-4 py-3">
    
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
        <div>
            <h3 class="mb-1 font-weight-bold text-dark">
                <i class="fas fa-file-import text-primary mr-1"></i> Import Danh Mục
            </h3>
            <p class="text-muted mb-0">Tải lên file Excel để thêm nhiều danh mục cùng lúc</p>
        </div>
        <div>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded px-3">
                <i class="fas fa-arrow-left mr-1"></i> Quay lại
            </a>
        </div>
    </div>
    
    <div class="row">
        
        <div class="col-lg-7 mb-4">
            <div class="card card-custom h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-info-circle text-info mr-1"></i>Hướng dẫn chi tiết</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-sm-7 pr-4 border-right">
                            <h6 class="font-weight-bold mb-3 text-secondary">Các bước thực hiện:</h6>
                            <ol class="list-unstyled pl-0 steps-list">
                                <li class="mb-3">
                                    <span class="step-number">1</span>
                                    <strong>Tải file Excel mẫu</strong>
                                    <p class="text-muted mb-0"><small>Sử dụng nút <b>Tải file mẫu</b> bên cạnh để lấy cấu trúc chuẩn.</small></p>
                                </li>
                                <li class="mb-3">
                                    <span class="step-number">2</span>
                                    <strong>Điền dữ liệu</strong>
                                    <p class="text-muted mb-1"><small>Điền <b>Tên danh mục</b> (bắt buộc) và <b>Mô tả</b> (tùy chọn).</small></p>
                                </li>
                                <li class="mb-3">
                                    <span class="step-number">3</span>
                                    <strong>Xem trước dữ liệu</strong>
                                    <p class="text-muted mb-0"><small>Upload và nhấn <b>Xem trước</b> để kiểm tra tính hợp lệ.</small></p>
                                </li>
                                <li>
                                    <span class="step-number">4</span>
                                    <strong>Lưu vào Database</strong>
                                    <p class="text-muted mb-0"><small>Chỉ những dòng hợp lệ mới được lưu.</small></p>
                                </li>
                            </ol>
                        </div>

                        <div class="col-sm-5 text-center d-flex flex-column justify-content-center align-items-center bg-light-soft rounded-lg p-3">
                            <i class="far fa-file-excel text-success mb-3" style="font-size: 60px;"></i>
                            <h5 class="font-weight-bold mb-2 text-dark">File Excel Mẫu</h5>
                            <p class="text-muted mb-3"><small>File cấu trúc chuẩn</small></p>
                            <a href="{{ route('admin.categories.import.template') }}" class="btn btn-success btn-md btn-block w-75 rounded">
                                <i class="fas fa-download mr-1"></i>Tải file mẫu
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top rounded-bottom-lg">
                    <strong class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Quy tắc Import:</strong>
                    <ul class="mb-0 mt-2 pl-3" style="font-size: 0.85rem;">
                        <li>Định dạng file: <b>.xlsx</b> hoặc <b>.xls</b> (Tối đa <b>2MB</b>).</li>
                        <li>Tên danh mục <b>không được trùng</b>.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card card-custom h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-cloud-upload-alt text-success mr-1"></i>Upload File Excel</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="upload-area border-2 border-dashed rounded-lg p-5 text-center bg-white" id="uploadArea">
                            <i class="fas fa-upload text-primary mb-3" style="font-size: 56px;"></i>
                            <h5 class="mb-2 font-weight-bold text-dark" id="uploadText">Kéo thả file vào đây</h5>
                            <p class="text-muted mb-3"><small>Hoặc nhấn vào khung để chọn file</small></p>
                            <div class="custom-file d-none" style="max-width: 300px; margin: 0 auto;">
                                <input type="file" class="custom-file-input" id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                                <label class="custom-file-label rounded" for="excel_file">Chọn file Excel...</label>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-md rounded px-5 shadow-sm" id="btnPreview">
                                <i class="fas fa-eye mr-1"></i>Xem trước dữ liệu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5" id="previewSection" style="display: none;">
        <div class="col-12">
            <div class="card card-custom"> 
                <div class="card-header bg-white border-bottom pt-3 pb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-dark"><i class="fas fa-table text-primary mr-1"></i>Dữ liệu Xem trước</h5>
                        <div id="previewStats"></div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clean table-hover mb-0" id="previewTable">
                            <thead class="bg-light">
                                <tr>
                                    <th width="60" class="text-center">Dòng</th>
                                    <th width="28%">Tên danh mục</th>
                                    <th width="35%">Mô tả</th>
                                    <th width="15%">Slug</th>
                                    <th width="100" class="text-center">Trạng thái</th>
                                    <th width="80" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                            </tbody>
                        </table>
                    </div>

                    <div id="errorSection" class="alert alert-danger m-2 rounded-lg" style="display: none;">
                        <h6 class="alert-heading font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i>Danh sách lỗi tổng hợp:</h6>
                        <ul id="errorList" class="mb-0 pl-4" style="font-size: 0.9rem;"></ul>
                    </div>

                    <div class="p-4 text-right border-top bg-light-soft">
                        <button type="button" class="btn btn-outline-secondary mr-1 rounded px-4" id="btnCancelPreview">
                            <i class="fas fa-times mr-1"></i>Hủy Preview
                        </button>
                        <button type="button" class="btn btn-success btn-md rounded px-5 shadow-sm" id="btnSave" disabled>
                            <i class="fas fa-database mr-1"></i>Lưu vào Database
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Global Styles for Clean UI */
    .card-custom {
        border-radius: 0.75rem !important; /* Bo góc mềm */
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important; /* Shadow mỏng */
        border: 1px solid #e9ecef !important; /* Border nhẹ */
    }
    .rounded-lg {
        border-radius: 0.75rem !important; 
    }
    .rounded-pill {
        border-radius: 50rem !important;
    }
    .border-dashed {
        border-style: dashed !important;
        border-color: #ced4da !important;
    }
    .bg-light-soft {
        background-color: #f8f9fa !important; /* Nền xám rất nhẹ */
    }

    /* Step List Tùy chỉnh */
    .steps-list {
        counter-reset: step-counter;
        list-style: none;
        padding-left: 0;
    }
    .steps-list li {
        position: relative;
        padding-left: 35px; /* Khoảng cách cho số */
    }
    .step-number {
        counter-increment: step-counter;
        position: absolute;
        left: 0;
        top: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background-color: #007bff; /* Primary color */
        color: white;
        font-weight: bold;
        font-size: 0.8rem;
    }

    /* Upload Area */
    .upload-area {
        cursor: pointer;
        transition: all 0.2s ease;
        border-color: #ced4da !important; 
    }
    
    .upload-area:hover {
        border-color: #007bff !important; /* Màu Primary khi hover */
        background: #f0f8ff !important; /* Nền xanh rất nhẹ */
    }

    /* Bảng Preview - Clean Table */
    .table-clean thead th {
        border-top: none; 
        border-bottom: 2px solid #e9ecef !important; /* Chỉ giữ lại border dưới */
        font-weight: 600;
        font-size: 0.9rem;
    }
    .table-clean tbody td {
        border-top: none; 
        vertical-align: middle;
        padding: 0.75rem 0.75rem;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #fcfcfc; /* Nền trắng gần như tinh khiết */
    }
    .table-hover tbody tr:hover {
        background-color: #f1f3f5 !important;
    }
    
    /* Row Colors - Màu sắc dịu nhẹ */
    .row-error {
        background-color: #fff8f8 !important; /* Đỏ cực nhạt */
    }
    .row-error:hover {
        background-color: #ffe9e9 !important;
    }
    
    .row-success {
        background-color: #f8fff8 !important; /* Xanh cực nhạt */
    }
    .row-success:hover {
        background-color: #e9fff4 !important;
    }
    
    /* Badge Colors - Pill Style */
    .badge-base {
        padding: .4em .8em;
        border-radius: 50rem; /* Bo tròn hoàn toàn */
        font-weight: 600;
        font-size: 75%;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
    }
    
    .badge-error {
        background-color: #dc3545;
        color: white;
        /* Kế thừa từ badge-base */
    }
    
    .badge-success {
        background-color: #28a745;
        color: white;
        /* Kế thừa từ badge-base */
    }
    .badge-primary {
        background-color: #007bff;
        color: white;
        /* Kế thừa từ badge-base */
    }

    /* Custom File Input */
    .custom-file-label {
        border-radius: 50rem !important;
        border: 1px solid #ced4da;
        background-color: white;
        color: #6c757d;
        text-align: left;
    }

    .custom-file-label.selected {
        border-color: #007bff;
        background-color: #e0f0ff; /* Nền xanh nhạt khi chọn */
        color: #007bff;
        font-weight: 500;
    }
    
    /* Code Styles (Slug) */
    code {
        background: #f1f1f1;
        padding: 2px 6px;
        border-radius: 3px;
        color: #1a73e8; 
        font-size: 0.8rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let previewData = [];
    
    // Drag and drop functionality
    const uploadArea = $('#uploadArea');
    const fileInput = $('#excel_file');
    const uploadText = $('#uploadText');
    const defaultUploadText = 'Kéo thả file vào đây';
    
    uploadArea.on('click', function(e) {
        if (!$(e.target).closest('.custom-file').length) {
             fileInput.click();
        }
    });
    
    // Xử lý Drag Over/Leave/Drop
    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css({
            'border-color': '#007bff',
            'background': '#f0f8ff',
        });
    });
    
    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css({
            'border-color': '#ced4da',
            'background': 'white',
        });
    });
    
    uploadArea.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).css({
            'border-color': '#ced4da',
            'background': 'white',
        });
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            fileInput.trigger('change');
        }
    });
    
    // Update file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $(this).siblings('.custom-file-label').addClass('selected').html(
                '<i class="fas fa-file-excel mr-1"></i>' + fileName
            );
            uploadText.html(`<i class="fas fa-check-circle text-success mr-1"></i> File đã chọn: <strong>${fileName}</strong>`);
            uploadArea.css({
                'border-color': '#007bff', 
                'background': '#e0f0ff',
            });
        } else {
            $(this).siblings('.custom-file-label').removeClass('selected').html('Chọn file Excel...');
            uploadText.html(defaultUploadText);
            uploadArea.css({
                'border-color': '#ced4da', 
                'background': 'white',
            });
        }
    });
    
    // Preview form submit
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const fileInput = $('#excel_file')[0];
        if (!fileInput.files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa chọn file',
                text: 'Vui lòng chọn file Excel để tải lên!'
            });
            return;
        }
        
        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');
        
        Swal.fire({
            title: 'Đang xử lý...',
            text: 'Vui lòng đợi trong giây lát',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("admin.categories.import.preview") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    previewData = response.data;
                    displayPreview(response);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                let message = 'Có lỗi xảy ra khi xử lý file!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: message
                });
            }
        });
    });
    
    // Display preview
    function displayPreview(response) {
        const { data, total_rows, valid_rows, error_rows, errors } = response;
        
        $('#previewSection').slideDown(500);
        
        // Build table
        let tableHtml = '';
        data.forEach((row, index) => {
            const rowClass = row.has_error ? 'row-error' : 'row-success';
            const statusBadge = row.has_error 
                ? '<span class="badge badge-base badge-error"><i class="fas fa-exclamation-circle mr-1"></i> Lỗi</span>' 
                : '<span class="badge badge-base badge-success"><i class="fas fa-check-circle mr-1"></i> OK</span>';
            
            let errorText = '';
            if (row.errors && row.errors.length > 0) {
                errorText = '<br><small class="text-danger font-weight-bold" style="font-size:0.8rem">Lỗi: ' + row.errors.join(', ') + '</small>';
            }
            
            // Nút xóa chỉ hiện với dòng lỗi
            const deleteBtn = row.has_error 
                ? `<button type="button" class="btn btn-sm btn-danger btn-delete-row rounded-pill" data-index="${index}" title="Xóa dòng này">
                    <i class="fas fa-times"></i>
                   </button>` 
                : '<span class="text-muted">-</span>';
            
            tableHtml += `
                <tr class="${rowClass}" data-row-index="${index}">
                    <td class="text-center font-weight-bold">${row.row_number}</td>
                    <td>${row.name || '<em class="text-muted">Trống</em>'}${errorText}</td>
                    <td class="text-muted">${row.description || '<em class="text-muted">Không có</em>'}</td>
                    <td><code>${row.slug || ''}</code></td>
                    <td class="text-center">${statusBadge}</td>
                    <td class="text-center">${deleteBtn}</td>
                </tr>
            `;
        });
        
        $('#previewTableBody').html(tableHtml);
        
        // Update stats và error section
        updatePreviewStats();

        // Scroll to preview
        $('html, body').animate({
            scrollTop: $('#previewSection').offset().top - 100
        }, 500);
    }
    
    // Xóa dòng lỗi
    $(document).on('click', '.btn-delete-row', function() {
        const index = $(this).data('index');
        const rowElement = $(`tr[data-row-index="${index}"]`);
        
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc muốn xóa dòng lỗi này? Hành động này sẽ loại bỏ dòng này khỏi quá trình Import.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                // Xóa khỏi mảng previewData (Lưu ý: giữ nguyên thứ tự index trong mảng)
                previewData[index].deleted = true; // Đánh dấu xóa mềm
                
                // Xóa dòng khỏi table với animation
                rowElement.fadeOut(300, function() {
                    $(this).remove();
                    
                    // Cập nhật lại stats và button
                    updatePreviewStats();
                });
            }
        });
    });
    
    // Hàm cập nhật stats sau khi xóa
    function updatePreviewStats() {
        const activeData = previewData.filter(row => !row.deleted);
        const total = activeData.length;
        const valid = activeData.filter(row => !row.has_error).length;
        const errors = total - valid;
        
        // Cập nhật thống kê
        $('#previewStats').html(`
            <span class="badge badge-base badge-primary mr-1">Tổng: ${total}</span>
            <span class="badge badge-base badge-success mr-1">Hợp lệ: ${valid}</span>
            <span class="badge badge-base badge-error">Lỗi: ${errors}</span>
        `);
        
        // Xử lý Error List
        if (errors > 0) {
            // Hiển thị lại error list chỉ với các lỗi chưa bị xóa
            let errorHtml = '';
            activeData.filter(row => row.has_error).forEach(row => {
                 errorHtml += `<li>Dòng ${row.row_number}: ${row.errors.join(', ')}</li>`;
            });
            $('#errorList').html(errorHtml);
            $('#errorSection').slideDown();
            
            $('#btnSave').prop('disabled', true).html('<i class="fas fa-times mr-1"></i> Lỗi, Không Lưu');
        } else if (valid > 0) {
            $('#errorSection').slideUp();
            $('#btnSave').prop('disabled', false).html(`<i class="fas fa-database mr-1"></i> Lưu ${valid} Danh Mục`);
        } else {
            $('#errorSection').slideUp();
            $('#btnSave').prop('disabled', true).html('<i class="fas fa-times mr-1"></i> Không Có Dữ Liệu');
        }
    }
    
    // Cancel preview
    $('#btnCancelPreview').on('click', function() {
        $('#previewSection').slideUp(300, function() {
            // Reset form và trạng thái sau khi ẩn
            $('#uploadForm')[0].reset();
            $('.custom-file-label').removeClass('selected').html('Chọn file Excel...');
            uploadText.html(defaultUploadText);
            uploadArea.css({
                'border-color': '#ced4da', 
                'background': 'white',
            });
        });
        previewData = [];
    });
    
    // Save to database
    $('#btnSave').on('click', function() {
        
        // Lấy dữ liệu hợp lệ và chưa bị xóa
        const validData = previewData.filter(row => !row.has_error && !row.deleted);
        
        if (validData.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Không có dữ liệu hợp lệ',
                text: 'Không có dòng dữ liệu nào hợp lệ để lưu!'
            });
            return;
        }
        
        Swal.fire({
            title: 'Xác nhận Import',
            html: `Bạn có chắc muốn import <strong>${validData.length}</strong> danh mục hợp lệ?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Có, import ngay!',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                processImport(validData);
            }
        });
    });
    
    // Process import
    function processImport(data) {
        Swal.fire({
            title: 'Đang import...',
            text: 'Quá trình có thể mất vài giây. Vui lòng đợi.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("admin.categories.import.process") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                data: data
            },
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("admin.categories.index") }}';
                    });
                } else {
                    let errorHtml = '<ul class="text-left">';
                    if (response.errors && response.errors.length > 0) {
                        response.errors.forEach(error => {
                            errorHtml += `<li>${error}</li>`;
                        });
                    }
                    errorHtml += '</ul>';
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi Import Dữ liệu',
                        html: response.message + errorHtml
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                let message = 'Có lỗi xảy ra khi import dữ liệu!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi Hệ thống',
                    text: message
                });
            }
        });
    }
});
</script>
@endpush
@endsection