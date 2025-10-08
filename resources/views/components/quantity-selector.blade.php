@props([
'id' => 'qtyInput',
'name' => 'qty',
'value' => 1,
'min' => 1,
'max' => $max,
'size' => 'sm', {{-- sm, md, lg --}}
])

<div class="input-group input-group-{{ $size }}" style="width: 100px;">
    <button class="btn btn-outline-secondary" type="button" onclick="changeQty('{{ $id }}', -1)">
        <i class="bi bi-dash"></i>
    </button>
    <input type="text"
        id="{{ $id }}"
        name="{{ $name }}"
        class="form-control text-center w-4 border border-dark rounded-0"
        value="{{ $value }}"
        min="{{ $min }}"
        max="{{ $max }}"
        step="1"
        inputmode="numeric"
        pattern="[0-9]*"
        aria-describedby="{{ $id }}-msg">
    <button class="btn btn-outline-secondary" type="button" onclick="changeQty('{{ $id }}', 1)">
        <i class="bi bi-plus"></i>
    </button>
</div>
<div id="{{ $id }}-msg" class="form-text text-danger d-none">Số lượng tối đa là {{ $max }}.</div>
@push('scripts')
<script>
    (function() {
        // Guard: only define helpers once
        if (!window.__qtyHelpersDefined) {
            window.__qtyHelpersDefined = true;
            window.validateQty = function(id, min, max, showMsg = true) {
                const input = document.getElementById(id);
                if (!input) return;
                const msg = document.getElementById(id + '-msg');
                let v = parseInt(input.value.replace(/[^0-9]/g, ''), 10);
                if (isNaN(v)) v = min;
                if (v < min) v = min;
                let exceeded = false;
                if (v > max) {
                    v = max;
                    exceeded = true;
                }
                input.value = v;
                // disable/enable buttons if present
                const group = input.closest('.input-group');
                if (group) {
                    const btns = group.querySelectorAll('button');
                    btns.forEach(btn => {
                        if (btn && btn.onclick && String(btn.onclick).includes("changeQty('" + id + "', 1)")) {
                            btn.disabled = v >= max;
                        }
                        if (btn && btn.onclick && String(btn.onclick).includes("changeQty('" + id + "', -1)")) {
                            btn.disabled = v <= min;
                        }
                    });
                }
                if (msg) {
                    if (showMsg && exceeded) {
                        msg.classList.remove('d-none');
                    } else {
                        msg.classList.add('d-none');
                    }
                }
                return v;
            };
            window.changeQty = function(id, delta) {
                const input = document.getElementById(id);
                if (!input) return;
                const min = parseInt(input.getAttribute('min') || '1', 10);
                const max = parseInt(input.getAttribute('max') || '9999', 10);
                const current = parseInt(input.value || min, 10) || min;
                let next = current + delta;
                if (next < min) next = min;
                if (next > max) next = max;
                input.value = next;
                validateQty(id, min, max, false);
            };
            window.initQtyControl = function(id) {
                const input = document.getElementById(id);
                if (!input) return;
                const min = parseInt(input.getAttribute('min') || '1', 10);
                const max = parseInt(input.getAttribute('max') || '9999', 10);
                // Prevent non-digit typing
                input.addEventListener('keydown', function(e) {
                    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Home', 'End', 'Tab'];
                    if (allowedKeys.includes(e.key)) return;
                    if ((e.ctrlKey || e.metaKey) && ['a', 'c', 'v', 'x'].includes(e.key.toLowerCase())) return;
                    // Allow digits only
                    if (!/^[0-9]$/.test(e.key)) {
                        e.preventDefault();
                    }
                });
                // Clamp on input
                input.addEventListener('input', function() {
                    validateQty(id, min, max, true);
                });
                // Initialize state
                validateQty(id, min, max, false);
            };
        }
        // Initialize this instance
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                window.initQtyControl('{{ $id }}');
            });
        } else {
            window.initQtyControl('{{ $id }}');
        }
    })();
</script>
@endpush