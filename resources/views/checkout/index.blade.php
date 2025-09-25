@extends('layouts.app')
@section('title', 'Thanh toán')

@section('content')
<div class="container">
    <h5 class="fw-semibold mb-3">Thanh toán</h5>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form class="row g-3" method="POST" action="{{ route('checkout.place') }}">
                        @csrf
                        <div class="col-12 col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="customer_email" value="{{ old('customer_email') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Địa chỉ</label>
                            <input type="text" name="customer_address" value="{{ old('customer_address') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phương thức thanh toán</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="COD" @selected(old('payment_method')==='COD')>COD</option>
                                <option value="MOMO" @selected(old('payment_method')==='MOMO')>MOMO</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 d-flex align-items-end">
                            <button class="btn btn-brand w-100">Đặt hàng</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-semibold">Tóm tắt đơn</h6>
                    @foreach(($items ?? []) as $line)
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ $line['product']->name }} x {{ $line['qty'] }}</span>
                            <span>{{ number_format($line['subtotal'],0,',','.') }}₫</span>
                        </div>
                    @endforeach
                    <div class="d-flex justify-content-between mt-2"><strong>Tổng</strong><strong>{{ number_format($total ?? 0,0,',','.') }}₫</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


