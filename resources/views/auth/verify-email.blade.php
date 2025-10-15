@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card border-0 shadow-lg rounded-4 fade-up">
        <div class="card-body p-4">
          <h4 class="fw-bold text-center mb-3 text-dark">Xác nhận email</h4>
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

            <div class="otp-inputs d-flex justify-content-between mb-4">
              @for ($i = 0; $i < 6; $i++)
                <input type="text" maxlength="1" inputmode="numeric"
                  class="form-control form-control-lg text-center otp-box"
                  data-index="{{ $i }}">
              @endfor
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-primary btn-lg flex-grow-1 verify-btn" id="submit-btn">
                <span id="btn-text" class="fw-semibold">Xác nhận (60s)</span>
              </button>
              <button type="button" class="btn btn-lg resend-btn" id="resend-btn" disabled>Gửi lại mã</button>
            </div>

            <div id="expired-msg" class="text-danger text-center mt-3" style="display:none; font-size: 14px;">
              <strong><i class="bi bi-exclamation-circle-fill"></i> Mã đã hết hạn. Vui lòng đăng ký lại hoặc yêu cầu gửi lại mã.</strong>
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
  .otp-box {
    width: 52px;
    height: 60px;
    font-size: 1.5rem;
    font-weight: 500;
    color: #333;
    border-radius: 0.75rem;
    border: 2px solid #e4e4e4;
    transition: all 0.2s ease;
  }
  .otp-box:focus {
    border-color: #EE4D2D;
    box-shadow: 0 0 0 3px rgba(238,77,45,0.25);
  }
  .otp-inputs {
    gap: 0.6rem;
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
</style>
@endsection

@push('scripts')
<script>
(() => {
  const inputs = document.querySelectorAll('.otp-box');
  const hiddenInput = document.getElementById('code');
  const form = document.getElementById('verify-form');
  const btnText = document.getElementById('btn-text');
  const resendBtn = document.getElementById('resend-btn');
  const expiredMsg = document.getElementById('expired-msg');
  const submitBtn = document.getElementById('submit-btn');
  let expiresAt = {{ (int)($expiresAt ?? (time() + 60)) }} * 1000;
  let ticking = false;

  // Gộp 6 ô OTP
  inputs.forEach((input, idx) => {
    input.addEventListener('input', e => {
      e.target.value = e.target.value.replace(/\D/g, '');
      if (e.target.value && idx < inputs.length - 1) inputs[idx + 1].focus();
      updateHidden();
    });
    input.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !input.value && idx > 0) inputs[idx - 1].focus();
    });
    input.addEventListener('paste', e => {
      e.preventDefault();
      const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      if (pasted.length === 6) {
        [...pasted].forEach((ch, i) => inputs[i] && (inputs[i].value = ch));
        updateHidden();
        inputs[5].focus();
      }
    });
  });
  function updateHidden() {
    hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
  }

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
        inputs.forEach(i => i.value = '');
        updateHidden();
        expiresAt = (data.expires_at || Math.floor(Date.now()/1000) + 60) * 1000;
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
