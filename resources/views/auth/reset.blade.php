@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card card-hover">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Đặt lại mật khẩu</h5>
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="{{ request('email', old('email')) }}" required>
                            @error('email')
                                <x-input-error :message="$message" />
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" class="form-control" name="password" required>
                            @error('password')
                                <x-input-error :message="$message" />
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                            @error('password_confirmation')
                                <x-input-error :message="$message" />
                            @enderror
                        </div>
                        @error('token')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <button class="btn btn-brand w-100">Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection