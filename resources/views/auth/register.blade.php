@extends('layouts.app')
@section('title', 'Đăng ký')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-sm card-hover fade-up">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3 text-center">Tạo tài khoản</h5>
                    @if ($errors->any())
                        <div class="alert alert-danger small">@foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach</div>
                    @endif
                    <form method="POST" action="{{ route('register.post') }}" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Họ và tên</label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
                                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="username" class="form-label">Tên người dùng (tuỳ chọn)</label>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" class="form-control" maxlength="50">
                                @error('username')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="form-control" required maxlength="15">
                                @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="address" class="form-label">Địa chỉ</label>
                                <input id="address" type="text" name="address" value="{{ old('address') }}" class="form-control" required maxlength="255">
                                @error('address')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label">Mật khẩu</label>
                                <input id="password" type="password" name="password" class="form-control" required>
                                @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-brand w-100 mt-3">Đăng ký</button>
                    </form>
                    <div class="text-center text-muted my-3">Hoặc</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('oauth.redirect', 'google') }}" class="btn btn-outline-secondary"><i class="bi bi-google me-2"></i>Đăng ký với Google</a>
                        <a href="{{ route('oauth.redirect', 'facebook') }}" class="btn btn-outline-secondary"><i class="bi bi-facebook me-2"></i>Đăng ký với Facebook</a>
                    </div>
                    <div class="text-center mt-3 small">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    input, button { transition: box-shadow .2s ease, transform .1s ease; }
    input:focus { box-shadow: 0 0 0 .2rem rgba(195,155,211,.25); }
    .btn-brand:hover { transform: translateY(-1px); }
</style>
@endpush


