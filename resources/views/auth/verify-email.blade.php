@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card border-0 shadow-lg rounded-4 fade-up">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-center mb-3 text-dark">Xác nhận email</h3>
                    <p class="text-center text-muted mb-4">
                        Mã xác nhận đã được gửi đến
                        <strong class="text-dark">{{ $email }}</strong>
                    </p>

                    @if ($errors->any())
                    <x-alert type="danger">
                        @foreach ($errors->all() as $error)
                        <i class="bi bi-exclamation-circle-fill"></i> {{ $error }}
                        @endforeach
                    </x-alert>
                    @endif

                    @if (session('status'))
                    <x-alert type="success"><i class="bi bi-check-circle-fill"></i> {{ session('status') }}</x-alert>
                    @endif

                    <form method="POST" action="{{ route('verify.email.submit') }}" id="verify-form" autocomplete="off">
                        @csrf
                        <input type="hidden" name="email" value="{{ $email }}">
                        <input type="hidden" id="code" name="code">

                        {{-- ✅ OTP input mới --}}
                        <div class="mb-4 position-relative text-center">
                            <input type="text" id="otp-display" 
                                   class="form-control form-control-lg text-center otp-single" 
                                   inputmode="numeric" maxlength="6" 
                                   placeholder="Nhập mã gồm 6 số">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1 verify-btn" id="submit-btn">
                                <span id="btn-text" class="fw-semibold">Xác nhận (60s)</span>
                            </button>
                            <button type="button" class="btn btn-lg resend-btn" id="resend-btn" disabled>Gửi lại mã</button>
                        </div>

                        <div id="expired-msg" class="text-danger text-center mt-3"
                            style="display:none; font-size: 14px;">
                            <strong><i class="bi bi-exclamation-circle-fill"></i> Mã đã hết hạn. Vui lòng đăng ký lại
                                hoặc yêu cầu gửi lại mã.</strong>
                        </div>

                        <div class="text-center mt-3">
                            <a href="{{ route('register') }}" class="re-register text-decoration-none small">
                                <i class="bi bi-arrow-right"></i> Quay lại trang đăng ký
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background: #fff;
    }

    .card {
        background: #fff;
    }

    /* ✅ 1 input duy nhất - spacing đều giữa các số */
    .otp-single {
        width: 100%;
        height: 60px;
        font-size: 2rem;
        font-weight: 700;
        color: #333;
        border-radius: 0.75rem;
        border: 2px solid #e4e4e4;
        text-align: center;
        letter-spacing: 1rem; /* cách đều giữa các số */
        caret-color: #EE4D2D;
        transition: all 0.25s ease;
        font-family: monospace;
    }

    .otp-single:focus {
        border-color: #EE4D2D;
        box-shadow: 0 0 0 3px rgba(238, 77, 45, 0.25);
        outline: none;
    }

    .otp-single::placeholder {
        color: #bbb;
        letter-spacing: normal;
        font-size: 1.2rem;
    }

    .verify-btn {
        background: #EE4D2D;
        color: #fff;
        border: none;
        transition: all 0.25s ease;
    }

    .verify-btn:hover {
        background: #D83B27;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
    }

    .verify-btn:disabled {
        background: #ccc !important;
        cursor: not-allowed;
    }

    .resend-btn {
        background: #EE4D2D;
        color: #fff;
        font-weight: 500;
        transition: all 0.25s ease;
    }

    .resend-btn:hover {
        background: #D83B27;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, .08);
    }

    .resend-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .re-register {
        color: #000;
        font-weight: 500;
        cursor: pointer;
    }

    .re-register:hover {
        color: #D83B27;
    }
    #otp-display::placeholder {
        color: #bbb;
        letter-spacing: normal;
        font-size: 1.2rem;
        line-height: 1.2rem;
    }
</style>
@endsection

@push('scripts')
<script>
(() => {
    const otpInput = document.getElementById('otp-display');
    const hiddenInput = document.getElementById('code');
    const btnText = document.getElementById('btn-text');
    const resendBtn = document.getElementById('resend-btn');
    const expiredMsg = document.getElementById('expired-msg');
    const submitBtn = document.getElementById('submit-btn');
    let expiresAt = {{ (int)($expiresAt ?? (time() + 60)) }} * 1000;
    let ticking = false;

    // ✅ Chỉ cho phép nhập 0-9, tối đa 6 số
    otpInput.addEventListener('input', e => {
        let value = e.target.value.replace(/\D/g, ''); // chỉ giữ số
        if (value.length > 6) value = value.slice(0, 6); // giới hạn 6 số
        e.target.value = value; // hiển thị lại (giữ nguyên spacing)
        hiddenInput.value = value; // lưu giá trị thực
    });

    // Countdown
    function tick() {
        if (!ticking) return;
        const now = Date.now();
        const left = Math.max(0, Math.floor((expiresAt - now) / 1000));
        btnText.textContent = `Xác nhận (${left}s)`;

        if (left <= 0) {
            ticking = false;
            submitBtn.disabled = true;
            btnText.textContent = 'Hết hạn';
            expiredMsg.style.display = 'block';
            resendBtn.disabled = false;
            return;
        }
        requestAnimationFrame(tick);
    }

    function startCountdown() {
        ticking = true;
        submitBtn.disabled = false;
        expiredMsg.style.display = 'none';
        resendBtn.disabled = true;
        requestAnimationFrame(tick);
    }

    startCountdown();

    // Gửi lại mã
    resendBtn.addEventListener('click', () => {
        resendBtn.disabled = true;
        const originalText = resendBtn.textContent;
        resendBtn.textContent = 'Đang gửi...';
        fetch("{{ route('verify.email.resend') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ email: "{{ $email }}" })
        })
        .then(r => r.json())
        .then(data => {
            resendBtn.textContent = originalText;
            if (data && data.canceled) {
                expiredMsg.textContent = data.message || 'Bạn đã vượt quá số lần gửi lại mã.';
                expiredMsg.style.display = 'block';
                setTimeout(() => { window.location.href = "{{ route('register') }}"; }, 1500);
                return;
            }
            if (data && data.success) {
                otpInput.value = '';
                hiddenInput.value = '';
                expiresAt = (data.expires_at || Math.floor(Date.now() / 1000) + 60) * 1000;
                startCountdown();
            } else {
                expiredMsg.textContent = data?.message || 'Không thể gửi lại mã. Vui lòng thử lại sau.';
                expiredMsg.style.display = 'block';
                resendBtn.disabled = false;
            }
        })
        .catch(() => {
            resendBtn.textContent = originalText;
            expiredMsg.textContent = 'Lỗi kết nối. Vui lòng thử lại.';
            expiredMsg.style.display = 'block';
            resendBtn.disabled = false;
        });
    });
})();
</script>
@endpush
