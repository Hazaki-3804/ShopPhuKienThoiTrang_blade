@extends('layouts.admin')
@section('title', 'Import Nhân Viên')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="container-fluid px-4 py-3">
    
    <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
        <div>
            <h3 class="mb-1 font-weight-bold text-dark">
                <i class="fas fa-file-import text-primary mr-1"></i> Import Nhân Viên
            </h3>
            <p class="text-muted mb-0">Tải lên file Excel để thêm nhiều nhân viên cùng lúc</p>
        </div>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded px-3">
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
                                    <p class="text-muted mb-1"><small>Điền đầy đủ thông tin: Họ tên, Email, SĐT, Địa chỉ, Mật khẩu, Trạng thái.</small></p>
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
                            <a href="{{ route('admin.users.import.template') }}" class="btn btn-success btn-md btn-block w-75 rounded">
                                <i class="fas fa-download mr-1"></i>Tải file mẫu
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-top rounded-bottom-lg">
                    <strong class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Quy tắc Import:</strong>
                    <ul class="mb-0 mt-2 pl-3" style="font-size: 0.85rem;">
                        <li>Định dạng file: <b>.xlsx</b> hoặc <b>.xls</b> (Tối đa <b>2MB</b>).</li>
                        <li>Email <b>không được trùng</b> với nhân viên đã có.</li>
                        <li>Mật khẩu phải có <b>tối thiểu 8 ký tự</b>, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt.</li>
                        <li>Trạng thái chọn từ dropdown: <b>Hoạt động</b> hoặc <b>Khóa</b>.</li>
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
                                    <th width="15%">Họ và tên</th>
                                    <th width="18%">Email</th>
                                    <th width="10%">Số điện thoại</th>
                                    <th width="20%">Địa chỉ</th>
                                    <th width="10%">Mật khẩu</th>
                                    <th width="8%" class="text-center">Trạng thái</th>
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
        border-radius: 0.75rem !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
        border: 1px solid #e9ecef !important;
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
        background-color: #f8f9fa !important;
    }

    /* Step List */
    .steps-list {
        counter-reset: step-counter;
        list-style: none;
        padding-left: 0;
    }
    .steps-list li {
        position: relative;
        padding-left: 35px;
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
        background-color: #007bff;
        color: white;
        font-weight: bold;
        font-size: 0.8rem;
    }

    /* Upload Area */
    .upload-area {
        cursor: pointer;
        transition: all 0.3s ease;
        min-height: 250px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .upload-area:hover {
        border-color: #007bff !important;
        background-color: #f8f9fa;
    }
    .upload-area.dragover {
        border-color: #28a745 !important;
        background-color: #e7f5e9;
    }

    /* Table Styles */
    .table-clean {
        font-size: 0.9rem;
    }
    .table-clean thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    .table-clean tbody tr {
        transition: background-color 0.2s ease;
    }
    .table-clean tbody tr:hover {
        background-color: #f8f9fa;
    }
    .table-clean tbody td {
        vertical-align: middle;
        padding: 0.75rem;
    }

    /* Badge Styles */
    .badge-custom {
        padding: 0.4em 0.75em;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 0.5rem;
    }

    /* Button Styles */
    .btn {
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
                `<i class="fas fa-file-excel text-success mr-2"></i>${fileName}`
            );
            uploadText.html(`<i class="fas fa-file-excel text-success mr-2"></i>${fileName}`);
        } else {
            $(this).siblings('.custom-file-label').removeClass('selected').html('Chọn file Excel...');
            uploadText.html(defaultUploadText);
        }
    });

    // Preview form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!fileInput[0].files.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa chọn file',
                text: 'Vui lòng chọn file Excel để xem trước!',
                confirmButtonColor: '#007bff'
            });
            return;
        }

        const formData = new FormData();
        formData.append('excel_file', fileInput[0].files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        $('#btnPreview').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Đang xử lý...');

        $.ajax({
            url: '{{ route("admin.users.import.preview") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    previewData = response.data;
                    displayPreview(response);
                    $('#previewSection').slideDown();
                    $('html, body').animate({
                        scrollTop: $('#previewSection').offset().top - 100
                    }, 500);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: error?.message || 'Có lỗi xảy ra khi xử lý file!',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                $('#btnPreview').prop('disabled', false).html('<i class="fas fa-eye mr-1"></i>Xem trước dữ liệu');
            }
        });
    });

    function displayPreview(response) {
        previewData = response.data;
        
        let tableHtml = '';
        response.data.forEach(function(row, index) {
            const rowClass = row.has_error ? 'table-danger' : '';
            const statusBadge = row.has_error 
                ? '<span class="badge badge-danger"><i class="fas fa-times-circle mr-1"></i>Lỗi</span>'
                : '<span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Hợp lệ</span>';
            
            const errorText = row.has_error 
                ? `<br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> ${row.errors.join(', ')}</small>`
                : '';
            
            const deleteBtn = row.has_error 
                ? `<button type="button" class="btn btn-sm btn-danger btn-delete-row rounded-pill" data-index="${index}" title="Xóa dòng này">
                    <i class="fas fa-times"></i>
                   </button>` 
                : '<span class="text-muted">-</span>';
            
            const statusBadgeDisplay = row.status === 1 
                ? '<span class="badge badge-success">Hoạt động</span>'
                : '<span class="badge badge-secondary">Khóa</span>';
            
            tableHtml += `
                <tr class="${rowClass}" data-row-index="${index}">
                    <td class="text-center font-weight-bold">${row.row_number}</td>
                    <td>${row.name || '<em class="text-muted">Trống</em>'}${errorText}</td>
                    <td>${row.email || '<em class="text-muted">Trống</em>'}</td>
                    <td>${row.phone || '<em class="text-muted">Trống</em>'}</td>
                    <td class="text-muted"><small>${row.address || '<em class="text-muted">Trống</em>'}</small></td>
                    <td><code>••••••••</code></td>
                    <td class="text-center">${statusBadgeDisplay}</td>
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
            text: 'Bạn có chắc muốn xóa dòng lỗi này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                // Đánh dấu xóa mềm
                previewData[index].deleted = true;
                
                // Xóa dòng khỏi table với animation
                rowElement.fadeOut(300, function() {
                    $(this).remove();
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
            <span class="badge badge-custom bg-primary text-white mr-1">Tổng: ${total}</span>
            <span class="badge badge-custom bg-success text-white mr-1">Hợp lệ: ${valid}</span>
            <span class="badge badge-custom bg-danger text-white">Lỗi: ${errors}</span>
        `);
        
        // Xử lý Error List
        if (errors > 0) {
            let errorHtml = '';
            activeData.filter(row => row.has_error).forEach(row => {
                 errorHtml += `<li>Dòng ${row.row_number}: ${row.errors.join(', ')}</li>`;
            });
            $('#errorList').html(errorHtml);
            $('#errorSection').slideDown();
            
            $('#btnSave').prop('disabled', true).html('<i class="fas fa-times mr-1"></i>Còn lỗi, không thể lưu');
        } else if (valid > 0) {
            $('#errorSection').slideUp();
            $('#btnSave').prop('disabled', false).html(`<i class="fas fa-database mr-1"></i>Lưu ${valid} nhân viên`);
        } else {
            $('#errorSection').slideUp();
            $('#btnSave').prop('disabled', true).html('<i class="fas fa-times mr-1"></i>Không có dữ liệu');
        }
    }

    // Cancel preview
    $('#btnCancelPreview').on('click', function() {
        $('#previewSection').slideUp(300, function() {
            $('#uploadForm')[0].reset();
            $('.custom-file-label').removeClass('selected').html('Chọn file Excel...');
            uploadText.html(defaultUploadText);
            uploadArea.css({
                'border-color': '#ced4da',
                'background': 'white'
            });
            previewData = [];
            $('#previewTableBody').empty();
            $('#errorList').empty();
        });
    });

    // Save to database
    $('#btnSave').on('click', function() {
        const validData = previewData.filter(row => !row.has_error && !row.deleted);

        if (validData.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Không có dữ liệu hợp lệ',
                text: 'Không có dòng nào hợp lệ để lưu!',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        Swal.fire({
            title: 'Xác nhận import',
            text: `Bạn có chắc muốn import ${validData.length} nhân viên?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                processImport(validData);
            }
        });
    });

    function processImport(data) {
        $('#btnSave').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Đang lưu...');

        $.ajax({
            url: '{{ route("admin.users.import.process") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                data: data
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        window.location.href = '{{ route("admin.users.index") }}';
                    });
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: error?.message || 'Có lỗi xảy ra khi import!',
                    confirmButtonColor: '#dc3545'
                });
                $('#btnSave').prop('disabled', false).html('<i class="fas fa-database mr-1"></i>Lưu vào Database');
            }
        });
    }
});
</script>
@endpush
@stop
