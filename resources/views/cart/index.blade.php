@extends('layouts.app')
@section('title', 'Giỏ hàng')

@section('content')
<div class="container">
    <h5 class="fw-semibold mb-3">Giỏ hàng</h5>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="list-group">
                @forelse($items as $line)
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-semibold">{{ $line['product']->name }}</div>
                            <div class="text-muted small">{{ number_format($line['price'],0,',','.') }}₫</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <form method="POST" action="{{ route('cart.update', $line['product']->id) }}" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input type="number" name="qty" value="{{ $line['qty'] }}" min="1" class="form-control" style="width:90px;">
                                <button class="btn btn-outline-secondary btn-sm">Cập nhật</button>
                            </form>
                            <form method="POST" action="{{ route('cart.remove', $line['product']->id) }}">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm">Xóa</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted">Giỏ hàng trống</div>
                @endforelse
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính</span>
                        <strong>{{ number_format($total,0,',','.') }}₫</strong>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="btn btn-brand w-100 mt-3">Thanh toán</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


