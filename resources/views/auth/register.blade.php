@extends('layouts.app')
@section('title', 'Đăng ký')
@push('styles')
<!-- CSS Choices.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">

<link href="{{ asset('css/register.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg rounded-4 fade-up">
                <div class="card-body p-4">
                    <h3 class="fw-semibold mb-3 text-center">Đăng ký</h3>
                    <p class="fs-6 text-center">Đăng ký tài khoản để sử dụng các tính năng của Shop</p>
                    @if ($errors->has('register_error'))
                    <div class="alert alert-danger alert-dismissible fade show fs-6" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ $errors->first('login_error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    <form method="POST" action="{{ route('register.post') }}" id="registerForm" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label label-input-important"><i class="bi bi-person-fill"></i> Họ và tên</label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
                                @error('name')
                                <x-input-error :message="$message" />
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="username" class="form-label"><i class="bi bi-person-badge"></i> Tên người dùng (tuỳ chọn)</label>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" class="form-control" maxlength="50">
                                @error('username')
                                <x-input-error :message="$message" />
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label label-input-important"><i class="bi bi-envelope-fill"></i> Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email" placeholder="abc@gmail.com">
                                @error('email')
                                <x-input-error :message=" $message" />
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label label-input-important"><i class="bi bi-phone-fill"></i> Số điện thoại</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-control" required maxlength="15" placeholder="090xxxxxxx">
                                @error('phone')
                                <x-input-error :message="$message" />
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="address" class="form-label label-input-important"><i class="bi bi-house-fill"></i> Địa chỉ</label>
                                <div class="row">
                                    <div class="col-12 col-md-6" style="padding: 0 8px 0 12px;">
                                        <select id="province" name="province" class="form-select" data-placeholder="-- Chọn tỉnh thành --">
                                            <option value="" disabled selected>-- Chọn tỉnh --</option>
                                        </select>
                                        @error('province')
                                        <x-input-error :message="$message" />
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-6" style="padding: 0 12px 0 8px;">
                                        <select id="ward" name="ward" class="form-select" data-placeholder="-- Chọn xã/phường --">
                                            <option value="" disabled selected>-- Chọn xã/phường --</option>
                                        </select>
                                        @error('ward')
                                        <x-input-error :message="$message" />
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label label-input-important"><i class="bi bi-house-fill"></i> Địa chỉ chi tiết</label>
                                <textarea id="address" type="text" name="address" value="{{ old('address') }}" class="form-control" required maxlength="255" rows="2" placeholder="Số nhà, tên đường, phường/xã, quận/huyện...">{{ old('address') }}</textarea>
                                @error('address')
                                <x-input-error :message="$message" />
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label label-input-important"><i class="bi bi-person-fill-lock"></i> Mật khẩu</label>
                                <x-input-password name="password" placeholder="Nhập mật khẩu" autocomplete="new-password" />
                                @error('password')
                                <x-input-error :message="$message" />
                                @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label label-input-important"><i class="bi bi-person-fill-lock"></i> Xác nhận mật khẩu</label>
                                <x-input-password name="password_confirmation" placeholder="Xác nhận mật khẩu" autocomplete="new-password" />
                                @error('password_confirmation')
                                <x-input-error :message="$message" />
                                @enderror
                            </div>
                        </div>
                        <x-cloudflare-captcha />
                        <div style="font-size: 14px;">
                            <input type="checkbox" class="form-check-input" id="termsCheck" name="terms">
                            <label class="form-check-label" for="termsCheck">
                                Tôi đồng ý với các <a href="{{ route('shop.index') }}">Điều khoản & Chính sách</a>
                            </label>
                            <div class="invalid-feedback">
                                Bạn phải đồng ý điều kiện trước khi tiếp tục.
                            </div>
                        </div>
                        <button type="submit" id="registerBtn" class="btn btn-brand w-100 mt-3">Đăng ký</button>
                    </form>
                    <div class="text-center mt-3 small">Bạn đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<!-- JS Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="{{ asset('js/register.js') }}"></script>
@endpush