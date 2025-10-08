@extends('layouts.admin')
@section('title', 'Quản lý bình luận')

@section('content_header')
<h1>Quản lý bình luận</h1>
@stop

@section('content')
<!-- Stats Cards -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Tổng bình luận</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                    <div class="text-primary" style="font-size: 2rem;">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đang hiển thị</h6>
                        <h3 class="mb-0 text-success">{{ $stats['visible'] }}</h3>
                    </div>
                    <div class="text-success" style="font-size: 2rem;">
                        <i class="bi bi-eye"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Đã ẩn</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['hidden'] }}</h3>
                    </div>
                    <div class="text-warning" style="font-size: 2rem;">
                        <i class="bi bi-eye-slash"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo nội dung, khách hàng, sản phẩm...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="visibility" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="visible" {{ request('visibility') === 'visible' ? 'selected' : '' }}>Đang hiển thị</option>
                    <option value="hidden" {{ request('visibility') === 'hidden' ? 'selected' : '' }}>Đã ẩn</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Tìm kiếm
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Reviews List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Danh sách bình luận ({{ $reviews->total() }})</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th style="width: 100px;">Đánh giá</th>
                        <th>Nội dung</th>
                        <th style="width: 120px;">Ngày tạo</th>
                        <th style="width: 100px;">Trạng thái</th>
                        <th style="width: 200px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr class="{{ $review->is_hidden ? 'table-warning' : '' }}">
                        <td>{{ $review->id }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $review->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($review->user->name) }}" 
                                     class="rounded-circle" width="40" height="40" alt="">
                                <div>
                                    <div class="fw-semibold">{{ $review->user->name }}</div>
                                    <div class="small text-muted">{{ $review->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $img = optional($review->product->product_images->first())->image_url ?? null;
                                    if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) {
                                        $img = asset($img);
                                    }
                                    $img = $img ?: 'https://via.placeholder.com/50';
                                @endphp
                                <img src="{{ $img }}" class="rounded border" width="50" height="50" style="object-fit: cover;" alt="">
                                <div class="text-truncate" style="max-width: 200px;">
                                    {{ $review->product->name }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill text-warning' : '' }}"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $review->rating }}/5</small>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 300px;" title="{{ $review->comment }}">
                                {{ $review->comment ?: '(Không có nội dung)' }}
                            </div>
                        </td>
                        <td>
                            <small>{{ $review->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            @if($review->is_hidden)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-eye-slash me-1"></i> Đã ẩn
                                </span>
                            @else
                                <span class="badge bg-success">
                                    <i class="bi bi-eye me-1"></i> Hiển thị
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <!-- Toggle visibility -->
                                <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}" class="d-inline toggle-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $review->is_hidden ? 'btn-success' : 'btn-warning' }}" 
                                            title="{{ $review->is_hidden ? 'Hiển thị' : 'Ẩn' }}"
                                            data-action="{{ $review->is_hidden ? 'hiển thị' : 'ẩn' }}">
                                        <i class="bi bi-eye{{ $review->is_hidden ? '' : '-slash' }}"></i>
                                    </button>
                                </form>

                                <!-- Delete -->
                                <button type="button" class="btn btn-sm btn-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal{{ $review->id }}"
                                        title="Xóa">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0">Chưa có bình luận nào</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reviews->hasPages())
    <div class="card-footer">
        {{ $reviews->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Delete Modals -->
@foreach($reviews as $review)
<div class="modal fade" id="deleteModal{{ $review->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa bình luận</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Bạn có chắc muốn xóa bình luận này?</p>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Lưu ý:</strong> Hành động này không thể hoàn tác!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="d-inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Xóa bình luận
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
.table-warning {
    background-color: #fff3cd !important;
}

/* Action buttons styling */
.btn-sm {
    width: 36px;
    height: 36px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-sm i {
    font-size: 1rem;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/toast.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle toggle visibility forms
    const toggleForms = document.querySelectorAll('.toggle-form');
    toggleForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const action = this.querySelector('button').dataset.action;
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PATCH'
                })
            })
            .then(response => response.json())
            .then(data => {
                showToast(`Đã ${action} bình luận thành công`, 'success');
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(error => {
                showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
            });
        });
    });

    // Handle delete forms
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            })
            .then(response => response.json())
            .then(data => {
                // Close modal
                const modal = bootstrap.Modal.getInstance(this.closest('.modal'));
                if (modal) modal.hide();
                
                showToast('Đã xóa bình luận thành công', 'success');
                setTimeout(() => window.location.reload(), 1000);
            })
            .catch(error => {
                showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
            });
        });
    });

    // Show toast on page load if there's a session message
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
});
</script>
@endpush
