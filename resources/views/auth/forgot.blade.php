@extends('layouts.app')
@section('title', 'Quên mật khẩu')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 fade-up">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3 text-center">Quên mật khẩu</h5>
                    @if (session('status'))
                    <x-alert type="success"><i class="bi bi-check-circle-fill"></i> {{ session('status') }} </x-alert>
                    @endif
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label label-input-important"><i class="bi bi-envelope-fill"></i> Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control" required autocomplete="email" autofocus>
                            @error('email')
                                <x-input-error :message="$message" />
                            @enderror
                        </div>
                        <x-cloudflare-captcha />
                        <button class="btn btn-brand w-100">Gửi liên kết</button>
                    </form>
                    <div class="text-center mt-3 small">Bạn đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection