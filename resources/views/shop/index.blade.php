@extends('layouts.app')
@section('title', 'Shop Của Hào Anh - Phụ kiện thời trang')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
<link rel="stylesheet" href="{{ asset('css/shop-index.css') }}">
@endpush
@section('content')
<div class="container py-4">
    @if(!empty($activeDiscount))
        @include('components.promo-banner', ['discount' => $activeDiscount])
    @endif
    <div class="row g-4">
        <div class="col-12 col-lg-3">
            <h5 class="fw-semibold"><i class="bi bi-card-list"></i> Danh mục sản phẩm</h5>
            <div class="card">
                <div class="card-body">
                    @include('components.category-menu', ['categories' => $categories ?? $sharedCategories])
                </div>
            </div>
            <h5 class="mt-2 fw-semibold"><i class="bi bi-cash-stack"></i> Giá</h5>
            <div class="card">
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <!-- Slider -->
                        <div id="price-slider"></div>
                        <!-- Hidden inputs để submit -->
                        <input type="hidden" name="price_min" id="price_min" value="{{ request('price_min', 0) }}">
                        <input type="hidden" name="price_max" id="price_max" value="{{ request('price_max', 1000000) }}">
                        <!-- Preserve current filters -->
                        @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        @if(request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif
                        @if(request('scope'))
                        <input type="hidden" name="scope" value="{{ request('scope') }}">
                        @endif

                        <!-- Hiển thị giá -->
                        <div class="d-flex justify-content-between small mt-2 mb-3">
                            <span id="price-min-label">{{ number_format(request('price_min', 0)) }}₫</span>
                            <span id="price-max-label">{{ number_format(request('price_max', 1000000)) }}₫</span>
                        </div>

                        <!-- Sort -->
                        <div class="col-12">
                            <select class="form-select form-select-sm" name="sort" id="sortSelect">
                                <option value="">Sắp xếp</option>
                                <option value="price_asc" @selected(request('sort')==='price_asc' )>Giá tăng dần</option>
                                <option value="price_desc" @selected(request('sort')==='price_desc' )>Giá giảm dần</option>
                                <option value="popular" @selected(request('sort')==='popular' )>Phổ biến</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="fw-semibold m-0"><i class="bi bi-boxes"></i> Sản phẩm</h5>
                    @if(request('q'))
                    <div class="small text-muted mt-1">Kết quả cho: <span class="badge bg-dark">"{{ request('q') }}"</span> <a href="{{ route('shop.index') }}" class="text-decoration-none ms-2">Xoá từ khoá</a></div>
                    @endif
                </div>
                <div class="small text-muted">{{ $products->total() }} sản phẩm</div>
            </div>
            <div class="row g-3">
                @forelse($products as $p)
                <div class="col-6 col-md-4">
                    @include('components.product-card', ['product' => ['id'=>$p->id,'name'=>$p->name,'category'=>$p->category->name ?? '', 'price'=> number_format($p->price,0,',','.') . '₫','image'=>$p->product_images[0]->image_url]])
                </div>
                @empty
                <div class="col-12 text-center text-muted py-5">
                    <div class="mb-2">Không tìm thấy sản phẩm phù hợp.</div>
                    @if(request('q'))
                    <div class="small">Hãy thử lại với từ khoá khác hoặc <a href="{{ route('shop.index') }}">xoá từ khoá</a>.</div>
                    @else
                    <div class="small">Hãy thay đổi bộ lọc hoặc thử lại sau.</div>
                    @endif
                    @endforelse
                </div>
                <x-pagination :paginator="$products" />
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/shop-index.js') }}"></script>
@endpush