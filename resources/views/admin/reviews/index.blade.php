@extends('layouts.admin')
@section('title', 'Quản lý bình luận')

@section('content_header')
<h1></h1>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-4 mb-3">
        <h4 class="fw-semibold m-0">Quản lý bình luận</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Quản lý bình luận']]" />
    </div>

    <!-- Stats Cards -->
    <div class="row mx-3 my-3">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card bg-primary text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                            <p class="mb-0">Tổng bình luận</p>
                        </div>
                        <div class="text-right">
                            <i class="bi bi-chat-dots" style="font-size: 2.5rem; opacity: 0.75;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card bg-success text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['visible'] }}</h3>
                            <p class="mb-0">Đang hiển thị</p>
                        </div>
                        <div class="text-right">
                            <i class="bi bi-eye" style="font-size: 2.5rem; opacity: 0.75;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card bg-warning text-white border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $stats['hidden'] }}</h3>
                            <p class="mb-0">Đã ẩn</p>
                        </div>
                        <div class="text-right">
                            <i class="bi bi-eye-slash" style="font-size: 2.5rem; opacity: 0.75;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="form-row align-items-center">
                
                <div class="col-lg-5 col-md-6 col-sm-12 mb-3 mb-md-0">
                    <label class="sr-only" for="search-input">Tìm kiếm</label> <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fa fa-search"></i></span> </div>
                        <input type="text" id="search-input" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo nội dung, khách hàng, sản phẩm...">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-12 mb-3 mb-md-0">
                    <label class="sr-only" for="status-select">Trạng thái</label>
                    <select id="status-select" name="visibility" class="form-control">
                        <option value="">-- Trạng thái: Tất cả --</option>
                        <option value="visible" {{ request('visibility') === 'visible' ? 'selected' : '' }}>Đang hiển thị</option>
                        <option value="hidden" {{ request('visibility') === 'hidden' ? 'selected' : '' }}>Đã ẩn</option>
                    </select>
                </div>

                <div class="col-lg-4 col-md-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fas fa-search mr-1"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo mr-1"></i> Làm mới
                    </a>
                </div>
                
            </form>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="card mx-3 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold">Danh sách bình luận ({{ $reviews->total() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle w-100">
                    <thead class="table-info">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th style="width: 100px;">Đánh giá</th>
                            <th>Nội dung</th>
                            <th style="width: 120px;">Ngày tạo</th>
                            <th style="width: 100px;">Trạng thái</th>
                            @canany(['hide reviews', 'delete reviews'])
                            <th style="width: 8%;">Thao tác</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                        <tr class="{{ $review->is_hidden ? 'table-warning' : 'table-info' }}">
                            <td>{{ $review->id }}</td>
                            <td>
                                <div>
                                    <div class="fw-semibold">{{ $review->user->name }}</div>
                                    <div class="small text-muted">{{ $review->user->email }}</div>
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
                                <p class="badge bg-warning text-dark">
                                    <i class="bi bi-eye-slash me-1"></i> Đã ẩn
                                </p>
                                @else
                                <span class="badge bg-success">
                                    <i class="bi bi-eye me-1"></i> Hiển thị
                                </span>
                                @endif
                            </td>
                           @canany(['hide reviews', 'delete reviews'])
                            <td>
                                <div class="dropdown text-center">
                                    <button class="btn btn-sm btn-light border-0" type="button" id="actionsMenu{{ $review->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="box-shadow:none;">
                                        <i class="fas fa-ellipsis-v text-secondary"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right shadow-sm border-0 rounded" aria-labelledby="actionsMenu{{ $review->id }}">
                                        
                                        @if(auth()->user()->can('hide reviews'))
                                        <form method="POST" action="{{ route('admin.reviews.toggle', $review) }}" class="toggle-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="dropdown-item" data-action="{{ $review->is_hidden ? 'hiển thị' : 'ẩn' }}">
                                                <i class="fas fa-eye{{ $review->is_hidden ? '' : '-slash' }} {{ $review->is_hidden ? 'text-info' : 'text-warning' }} mr-2"></i>
                                                {{ $review->is_hidden ? 'Hiển thị bình luận' : 'Ẩn bình luận' }}
                                            </button>
                                        </form>
                                        @endif

                                        @if(auth()->user()->can('delete reviews'))
                                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash mr-2"></i>Xóa bình luận
                                            </button>
                                        </form>
                                        @endif

                                    </div>
                                </div>
                            </td>
                            @endcanany
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
        <div class="card-footer bg-white">
            {{ $reviews->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
<style>
    /* Table styling */
    .table-warning {
        background-color: #fff3cd !important;
    }
    .table-info{
        background-color: #cfe2ff !important;
    }

    /* Form controls */
    .form-control:focus,
    .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

</style>
@endpush

@push('scripts')
<script src="{{ asset('js/admin/ajax-form-handler.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle toggle visibility forms
        const toggleForms = document.querySelectorAll('.toggle-form');
        toggleForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const action = this.querySelector('button').dataset.action;

                fetch(this.action, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        // Consider redirect/HTML as success too
                        if (response.ok || response.redirected || response.status === 0) {
                            if (typeof AjaxFormHandler !== 'undefined') {
                                AjaxFormHandler.showToast(`Đã ${action} bình luận thành công`, 'success');
                            }
                            setTimeout(() => window.location.reload(), 1200);
                            return;
                        }
                        const text = await response.text();
                        throw new Error(text || `HTTP ${response.status}`);
                    })
                    .catch(error => {
                        if (typeof AjaxFormHandler !== 'undefined') {
                            AjaxFormHandler.showToast('Có lỗi xảy ra, vui lòng thử lại', 'danger');
                        }
                    });
            });
        });

        // Handle delete forms via AJAX - delete immediately without modal
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                fetch(this.action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(async response => {
                        // Treat redirect/HTML as success too
                        if (response.ok || response.redirected || response.status === 0) {
                            if (typeof AjaxFormHandler !== 'undefined') {
                                AjaxFormHandler.showToast('Đã xóa bình luận thành công', 'success');
                            }
                            setTimeout(() => window.location.reload(), 1200);
                            return;
                        }
                        const text = await response.text();
                        throw new Error(text || `HTTP ${response.status}`);
                    })
                    .catch(() => {
                        if (typeof AjaxFormHandler !== 'undefined') {
                            AjaxFormHandler.showToast('Có lỗi xảy ra, vui lòng thử lại', 'danger');
                        }
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                    });
            });
        });

        // Show toast on page load if there's a session message
        @if(session('success'))
        if (typeof AjaxFormHandler !== 'undefined') {
            AjaxFormHandler.showToast('{{ session('
                success ') }}', 'success');
        }
        @endif
    });
</script>
@endpush