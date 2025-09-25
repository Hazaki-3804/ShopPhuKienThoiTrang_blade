@extends('layouts.app')
@section('title', 'Shop - Fasho')

@section('content')
<div class="container">
    <div class="row g-4">
        <div class="col-12 col-lg-3">
        <h5 class="fw-semibold">Danh mục</h6>

            <div class="card">
                <div class="card-body">
                    @include('components.category-menu', ['categories' => $categories ?? $sharedCategories])
                    <hr>
                    <h6 class="fw-semibold">Lọc theo giá</h6>
                    <form method="GET" class="row g-2">
                        <div class="col-6"><input class="form-control form-control-sm" name="price_min" type="number" placeholder="Min" value="{{ request('price_min') }}"></div>
                        <div class="col-6"><input class="form-control form-control-sm" name="price_max" type="number" placeholder="Max" value="{{ request('price_max') }}"></div>
                        <div class="col-12">
                            <select class="form-select form-select-sm" name="sort">
                                <option value="">Sắp xếp</option>
                                <option value="price_asc" @selected(request('sort')==='price_asc')>Giá tăng dần</option>
                                <option value="price_desc" @selected(request('sort')==='price_desc')>Giá giảm dần</option>
                                <option value="popular" @selected(request('sort')==='popular')>Phổ biến</option>
                            </select>
                        </div>
                        <div class="col-12"><button class="btn btn-brand w-100 btn-sm">Áp dụng</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="fw-semibold m-0">Sản phẩm</h5>
                <div class="small text-muted">{{ $products->total() }} kết quả</div>
            </div>
            <div class="row g-3">
                @forelse($products as $p)
                    <div class="col-6 col-md-4">
                        @include('components.product-card', ['product' => ['id'=>$p->id,'name'=>$p->name,'category'=>$p->category->name ?? '', 'price'=> number_format($p->price,0,',','.') . '₫','image'=>$p->image_url]])
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">Không có sản phẩm</div>
                @endforelse
            </div>
            <div class="mt-3 d-flex justify-content-center">{!! $products->onEachSide(1)->links() !!}</div>
        </div>
    </div>
</div>
@endsection


