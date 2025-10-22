@php
    // Expecting $discount is an instance of App\Models\Discount
    $code = $discount->code ?? '';
    $desc = $discount->description ?? '';
    $start = optional($discount->start_date)->format('d/m/Y');
    $end = optional($discount->end_date)->format('d/m/Y');
    $type = $discount->discount_type; // 'percent' | 'fixed' (assumption)
    $value = $type === 'percent' ? rtrim(rtrim(number_format($discount->discount_value, 2), '0'), '.') . '%' : number_format($discount->discount_value, 0, ',', '.').'đ';
    $key = 'promo_banner_'.($discount->id ?? 'x');
@endphp

<div id="promoBanner" class="promo-banner d-none" data-key="{{ $key }}">
    <div class="promo-inner">
        <button class="btn-close promo-close" aria-label="Close"></button>
        <div class="promo-badge">HOT</div>
        <div class="promo-head text-center">
            <div class="promo-title fw-bold">ƯU ĐÃI ĐẶC BIỆT</div>
            <div class="promo-sub">Mã: <span class="promo-code">{{ $code }}</span></div>
        </div>
        <div class="promo-body">
            <div class="promo-off">Giảm {{ $value }}</div>
            @if($desc)
                <div class="promo-desc small text-muted">{{ $desc }}</div>
            @endif
            <div class="promo-time small mt-2">
                <i class="bi bi-clock"></i>
                Hiệu lực: {{ $start ?: 'Ngay' }} - {{ $end ?: 'Không thời hạn' }}
            </div>
        </div>
        <div class="mt-3 d-grid">
            <a href="{{ route('shop.index') }}" class="btn btn-shopee fw-semibold">
                Sử dụng ngay
            </a>
        </div>
    </div>
</div>

<!-- Mini floating button (like chatbot) -->
<button id="promoFab" class="promo-fab d-none" aria-label="Khuyến mãi">
    <i class="bi bi-gift-fill"></i>
    <span class="promo-fab-badge">{{ $type === 'percent' ? (int) $discount->discount_value.'%' : 'Sale' }}</span>
    <span class="visually-hidden">Mã {{ $code }}</span>
  </button>

<style>
    .promo-banner{position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:1080; display:flex; align-items:center; justify-content:center; padding:16px}
    .promo-banner .promo-inner{position:relative; width:min(92vw, 420px); background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,.2); padding:20px; overflow:hidden}
    .promo-banner .promo-inner::before{content:""; position:absolute; inset:-40% -40% auto -40%; height:260px; background:linear-gradient(135deg,#ff7a59,#ff512f,#ff9a44); filter:blur(40px); opacity:.25;}
    .promo-badge{position:absolute; top:12px; left:12px; background:#ff4d2d; color:#fff; font-weight:700; padding:6px 10px; border-radius:999px; font-size:12px; box-shadow:0 6px 16px rgba(255,77,45,.35)}
    .promo-close{position:absolute; top:10px; right:10px}
    .promo-head{margin-top:8px}
    .promo-title{font-size:18px}
    .promo-sub{font-size:14px; margin-top:4px}
    .promo-code{background:#fff3cd; color:#8a6d3b; padding:2px 8px; border-radius:8px; font-weight:700}
    .promo-body{margin-top:12px; text-align:center}
    .promo-off{font-size:28px; font-weight:600; color:var(--accent); text-shadow:0 2px 0 rgba(0,0,0,.06); font-family: inherit}
    @media (min-width: 768px){.promo-off{font-size:32px}}
    /* Floating mini button */
    .promo-fab{position:fixed; right:96px; bottom:24px; width:56px; height:56px; border-radius:50%; border:none; background:linear-gradient(135deg,#ff7a59,#ff512f); color:#fff; display:flex; align-items:center; justify-content:center; box-shadow:0 10px 24px rgba(255,81,47,.4); z-index:2000}
    .promo-fab i{font-size:22px}
    .promo-fab-badge{position:absolute; top:-6px; right:-6px; background:#ffc107; color:#000; font-weight:700; font-size:11px; padding:2px 6px; border-radius:999px; box-shadow:0 4px 10px rgba(0,0,0,.15)}
    @media (max-width: 575.98px){.promo-fab{right:78px; bottom:18px; width:50px; height:50px}}
</style>

<script>
    (function(){
        const root = document.getElementById('promoBanner');
        const fab = document.getElementById('promoFab');
        if(!root || !fab) return;

        const id = root.getAttribute('data-key') || 'promo';
        const shownKey = id + '_shown'; // once per tab session

        function openBanner(){
            root.classList.remove('d-none');
            fab.classList.add('d-none');
        }
        function closeBanner(){
            root.classList.add('d-none');
            fab.classList.remove('d-none');
        }

        // Auto open only the first time in this tab session
        const alreadyShown = sessionStorage.getItem(shownKey) === '1';
        if (!alreadyShown) {
            setTimeout(()=>{
                openBanner();
                sessionStorage.setItem(shownKey, '1');
            }, 600);
        } else {
            // Do not auto-open again; just show the floating button
            fab.classList.remove('d-none');
        }

        // Interactions
        fab.addEventListener('click', openBanner);
        root.addEventListener('click', (e)=>{ if(e.target === root) closeBanner(); });
        root.querySelector('.promo-close')?.addEventListener('click', closeBanner);
    })();
</script>
