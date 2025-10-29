@extends('layouts.app')
@section('title', 'Giỏ hàng')

@section('content')
<div class="container py-4">
    <h5 class="fw-semibold mb-3">Giỏ hàng</h5>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="list-group">
                @forelse($items as $line)
                    @php
                        $img = optional($line['product']->product_images[0] ?? null)->image_url;
                        if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) {
                            $img = asset($img);
                        }
                        $img = $img ?: 'https://picsum.photos/120/120?random=' . $line['product']->id;
                    @endphp
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <input class="form-check-input cart-item-check" type="checkbox" name="selected[]" value="{{ $line['product']->id }}" data-price="{{ (int)$line['price'] }}" aria-label="Chọn sản phẩm" form="cartSelectForm"> 
                            <img src="{{ $img }}" alt="{{ $line['product']->name }}" class="cart-thumb rounded border">
                            <div>
                                <div class="fw-semibold cart-title">{{ $line['product']->name }}</div>
                                <div class="text-muted small">{{ number_format($line['price'],0,',','.') }}₫</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 cart-actions">
                            <form method="POST" action="{{ route('cart.update', $line['product']->id) }}" class="d-flex gap-2 align-items-center auto-update-form">
                                @csrf
                                @include('components.quantity-selector', [
                                    'id' => 'cart_qty_'.$line['product']->id,
                                    'name' => 'qty',
                                    'value' => $line['qty'],
                                    'min' => 1,
                                    'max' => $line['product']->stock,
                                    'size' => 'sm'
                                ])
                            </form>
                            <form method="POST" action="{{ route('cart.remove', $line['product']->id) }}" class="delete-form">
                                @csrf
                                <button class="btn btn-delete-shopee btn-sm"><i class="bi bi-trash"></i> Xóa</button>
                            </form>
                        </div>
                    </div>
                @empty
                <div class="alert alert-warning text-center w-100 my-2" role="alert">
                    <i class="bi bi-cart-x me-2"></i>
                    Giỏ hàng của bạn hiện tại không có sản phẩm nào!
                    <a href="{{ route('shop.index') }}" class="ms-2">Tiếp tục mua sắm</a>
                </div>
                @endforelse
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <!-- Standalone checkout form (no nesting) -->
                    <form id="cartSelectForm" method="GET" action="{{ route('checkout.index') }}"></form>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label" for="checkAll">Chọn tất cả</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Tạm tính</span>
                        <strong id="selectedTotalValue">{{ number_format($total,0,',','.') }}₫</strong>
                    </div>
                    <button type="submit" form="cartSelectForm" class="btn btn-brand w-100 mt-3">Thanh toán</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="cartDeleteConfirmModal" tabindex="-1" aria-labelledby="cartDeleteConfirmLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="cartDeleteConfirmLabel">Xác nhận xóa</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng không?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDeleteCart">Xóa</button>
      </div>
    </div>
  </div>
</div>

@if(isset($related) && $related->count())
<div class="container pb-4">
    <h6 class="fw-semibold mb-3">Sản phẩm tương tự</h6>
    <div class="row g-3">
        @foreach($related as $rel)
        <div class="col-6 col-md-4 col-lg-3">
            @include('components.product-card', ['product' => $rel])
        </div>
        @endforeach
    </div>
</div>
@endif
@push('styles')
<style>
    .cart-thumb {
        width: 64px;
        height: 64px;
        object-fit: cover;
    }

    @media (min-width: 992px) {
        .cart-thumb {
            width: 72px;
            height: 72px;
        }
    }

    /* Shopee-like delete button */
    .btn-delete-shopee {
        background: #fff;
        color: #EE4D2D;
        border: 1px solid #EE4D2D;
    }

    .btn-delete-shopee:hover,
    .btn-delete-shopee:focus {
        background: #EE4D2D;
        color: #fff;
        border-color: #EE4D2D;
    }

    /* Consistent layout and wrapping for product names and actions */
    .cart-title {
        width: 260px; /* default width for wrapping */
        white-space: normal;
        word-break: break-word;
        line-height: 1.25;
        margin-bottom: 2px;
    }
    @media (min-width: 576px) {
        .cart-title { width: 320px; }
    }
    @media (min-width: 992px) {
        .cart-title { width: 380px; }
    }
    .cart-actions {
        min-width: 160px;
        justify-content: flex-end;
    }
</style>
@endpush
@push('scripts')
<script>
    // Auto submit update form when quantity changes
    document.addEventListener('DOMContentLoaded', function() {
        // Disable original component event handlers first
        document.querySelectorAll('.qty-ig button').forEach(function(btn) {
            // Remove onclick attributes from component
            btn.removeAttribute('onclick');
        });
        
        // Manual quantity control for cart (bypass component issues)
        document.querySelectorAll('.qty-ig').forEach(function(qtyGroup) {
            const input = qtyGroup.querySelector('input[name="qty"]');
            const minusBtn = qtyGroup.querySelector('button:first-child');
            const plusBtn = qtyGroup.querySelector('button:last-child');
            
            if (!input || !minusBtn || !plusBtn) return;
            
            const min = parseInt(input.getAttribute('min') || '1');
            const max = parseInt(input.getAttribute('max') || '999');
            
            console.log('Manual setup for:', input.id, 'min:', min, 'max:', max);
            
            function updateValue(newValue) {
                if (newValue < min) newValue = min;
                if (newValue > max) newValue = max;
                input.value = newValue;
                
                // Trigger events
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Update button states
                minusBtn.disabled = newValue <= min;
                plusBtn.disabled = newValue >= max;
            }
            
            // Remove existing event listeners and add new ones
            const newMinusBtn = minusBtn.cloneNode(true);
            const newPlusBtn = plusBtn.cloneNode(true);
            minusBtn.parentNode.replaceChild(newMinusBtn, minusBtn);
            plusBtn.parentNode.replaceChild(newPlusBtn, plusBtn);
            
            // Minus button
            newMinusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const current = parseInt(input.value) || min;
                updateValue(current - 1);
            });
            
            // Plus button  
            newPlusBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const current = parseInt(input.value) || min;
                updateValue(current + 1);
            });
            
            // Input validation
            input.addEventListener('input', function() {
                let value = parseInt(this.value) || min;
                if (value < min) value = min;
                if (value > max) value = max;
                this.value = value;
                
                minusBtn.disabled = value <= min;
                plusBtn.disabled = value >= max;
            });
            
            // Initialize button states
            const currentValue = parseInt(input.value) || min;
            minusBtn.disabled = currentValue <= min;
            plusBtn.disabled = currentValue >= max;
        });
        
        // Sử dụng event delegation để lắng nghe tất cả thay đổi số lượng
        let submitTimeouts = {};
        
        function submitFormForInput(inp) {
            const form = inp.closest('form');
            if (form) {
                console.log('Submitting form for:', inp.id, 'after 800ms delay');
                clearTimeout(submitTimeouts[inp.id]);
                submitTimeouts[inp.id] = setTimeout(() => {
                    console.log('Actually submitting form for:', inp.id);
                    form.submit();
                }, 800); // Tăng delay lên 800ms để đảm bảo ổn định
            } else {
                console.log('No form found for:', inp.id);
            }
        }

        // Lắng nghe tất cả thay đổi trên container
        document.addEventListener('change', function(e) {
            if (e.target.matches('.auto-update-form input[name="qty"]')) {
                const inp = e.target;
                console.log('Change event detected for:', inp.id, 'value:', inp.value);
                
                // Auto check item khi thay đổi số lượng
                const row = inp.closest('.list-group-item');
                const cb = row?.querySelector('.cart-item-check');
                if (cb && !cb.checked) { 
                    cb.checked = true; 
                    syncCheckAllState(); 
                }
                
                // Cập nhật tổng tiền ngay lập tức
                recalcSelectedTotal();
                
                // Submit form để cập nhật database
                submitFormForInput(inp);
            }
        });

        // Lắng nghe input event để cập nhật tổng tiền ngay lập tức
        document.addEventListener('input', function(e) {
            if (e.target.matches('.auto-update-form input[name="qty"]')) {
                const inp = e.target;
                
                // Auto check item khi thay đổi số lượng
                const row = inp.closest('.list-group-item');
                const cb = row?.querySelector('.cart-item-check');
                if (cb && !cb.checked) { 
                    cb.checked = true; 
                    syncCheckAllState(); 
                }
                
                // Cập nhật tổng tiền ngay lập tức (không submit form)
                recalcSelectedTotal();
            }
        });

        // Lắng nghe blur event
        document.addEventListener('blur', function(e) {
            if (e.target.matches('.auto-update-form input[name="qty"]')) {
                const inp = e.target;
                const form = inp.closest('form');
                if (form) {
                    console.log('Blur event - submitting immediately for:', inp.id);
                    clearTimeout(submitTimeouts[inp.id]);
                    form.submit();
                }
            }
        }, true);

        // Backup: Theo dõi tất cả quantity inputs với MutationObserver
        document.querySelectorAll('.auto-update-form input[name="qty"]').forEach(function(inp) {
            let lastValue = inp.value;
            
            // Theo dõi thay đổi giá trị bằng MutationObserver
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                        const newValue = inp.value;
                        if (newValue !== lastValue) {
                            console.log('MutationObserver detected change for:', inp.id, 'from', lastValue, 'to', newValue);
                            lastValue = newValue;
                            
                            // Auto check item
                            const row = inp.closest('.list-group-item');
                            const cb = row?.querySelector('.cart-item-check');
                            if (cb && !cb.checked) { 
                                cb.checked = true; 
                                syncCheckAllState(); 
                            }
                            
                            // Cập nhật tổng tiền và submit
                            recalcSelectedTotal();
                            submitFormForInput(inp);
                        }
                    }
                });
            });
            
            observer.observe(inp, { attributes: true, attributeFilter: ['value'] });
            
            // Cũng theo dõi thay đổi property value
            setInterval(function() {
                if (inp.value !== lastValue) {
                    console.log('Polling detected change for:', inp.id, 'from', lastValue, 'to', inp.value);
                    lastValue = inp.value;
                    
                    // Auto check item
                    const row = inp.closest('.list-group-item');
                    const cb = row?.querySelector('.cart-item-check');
                    if (cb && !cb.checked) { 
                        cb.checked = true; 
                        syncCheckAllState(); 
                    }
                    
                    // Cập nhật tổng tiền và submit
                    recalcSelectedTotal();
                    submitFormForInput(inp);
                }
            }, 500);
        });
        // Delete confirm modal logic
        let deleteTargetForm = null;
        const modalEl = document.getElementById('cartDeleteConfirmModal');
        let bsModal = null;
        if (modalEl) bsModal = new bootstrap.Modal(modalEl);
        document.querySelectorAll('.delete-form .btn').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                const form = this.closest('form');
                if (!form || !bsModal) return form?.submit();
                deleteTargetForm = form;
                bsModal.show();
            });
        });
        const confirmBtn = document.getElementById('btnConfirmDeleteCart');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', function(){
                if (deleteTargetForm) deleteTargetForm.submit();
            });
        }
        // Select all handler
        const checkAll = document.getElementById('checkAll');
        const itemChecks = document.querySelectorAll('.cart-item-check');
        function syncCheckAllState(){
            if (!itemChecks.length) { if (checkAll) checkAll.checked = false; return; }
            const allChecked = Array.from(itemChecks).every(c => c.checked);
            const anyChecked = Array.from(itemChecks).some(c => c.checked);
            if (checkAll) {
                checkAll.indeterminate = anyChecked && !allChecked;
                checkAll.checked = allChecked;
            }
        }
        if (checkAll) {
            checkAll.addEventListener('change', function(){
                itemChecks.forEach(c => { c.checked = checkAll.checked; });
                recalcSelectedTotal();
            });
        }
        itemChecks.forEach(c => c.addEventListener('change', syncCheckAllState));
        
        // Auto-check tất cả sản phẩm khi load trang (để tính tổng tiền đúng)
        itemChecks.forEach(c => { c.checked = true; });
        
        syncCheckAllState();

        // Live subtotal update based on selection and qty
        function recalcSelectedTotal(){
            const totalEl = document.getElementById('selectedTotalValue');
            let sum = 0;
            document.querySelectorAll('.list-group-item').forEach(function(row){
                const cb = row.querySelector('.cart-item-check');
                if (!cb || !cb.checked) return;
                const price = parseInt(cb.getAttribute('data-price') || '0', 10) || 0;
                const qtyInput = row.querySelector('.auto-update-form input[name="qty"]');
                const qty = parseInt(qtyInput?.value || '1', 10) || 1;
                sum += price * qty;
            });
            if (totalEl) totalEl.textContent = new Intl.NumberFormat('vi-VN').format(sum) + '₫';
        }
        // Bind events
        itemChecks.forEach(c => c.addEventListener('change', function(){ syncCheckAllState(); recalcSelectedTotal(); }));
        document.querySelectorAll('.auto-update-form input[name="qty"]').forEach(function(inp){
            inp.addEventListener('change', function(){
                // If user adjusts qty, auto check the item for convenience
                const row = this.closest('.list-group-item');
                const cb = row?.querySelector('.cart-item-check');
                if (cb && !cb.checked) { cb.checked = true; syncCheckAllState(); }
                recalcSelectedTotal();
            });
            inp.addEventListener('input', recalcSelectedTotal);
        });
        recalcSelectedTotal();

        // Prevent submit if nothing selected
        const form = document.getElementById('cartSelectForm');
        if (form) {
            form.addEventListener('submit', function(e){
                // Checkboxes are outside the form but associated via form="cartSelectForm"
                const anyChecked = document.querySelectorAll('.cart-item-check:checked').length > 0;
                if (!anyChecked) {
                    e.preventDefault();
                    // Show toast notification
                    showToast('Vui lòng chọn ít nhất 1 sản phẩm để thanh toán', 'warning');
                }
            });
        }
    });
</script>
@endpush
@endsection