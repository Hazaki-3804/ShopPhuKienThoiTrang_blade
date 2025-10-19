@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 fade-up">
                <div class="card-body p-4">
                    <h3 class="fw-semibold mb-3 text-center">Đặt lại mật khẩu</h3>
                    <p class="fs-6 text-center">Vui lòng nhập thông tin sau để đặt lại mật khẩu</p>
                    <form method="POST" action="{{ route('password.update') }}" id="resetForm" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-envelope-fill"></i> Email</label>
                            <input type="email" class="form-control" name="email" value="{{ request('email', old('email')) }}" required>
                            @error('email')
                                <x-input-error :message="$message" />
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-person-fill-lock"></i> Mật khẩu mới</label>
                            <input type="password" class="form-control" name="password" required>
                            @error('password')
                                <x-input-error :message="$message" />
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-person-fill-lock"></i> Xác nhận mật khẩu</label>
                            <input type="password" class="form-control" name="password_confirmation" required>
                            @error('password_confirmation')
                                <x-input-error :message="$message" />
                            @enderror
                        </div>
                        @error('token')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <button class="btn btn-brand w-100"id='resetBtn'>Cập nhật</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function btn_loading(formId, btnId) {
        const form = document.getElementById(formId);
        const btn = document.getElementById(btnId);
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang cập nhật';
            });
        }
    }
    btn_loading('resetForm', 'resetBtn');
</script>
@endpush