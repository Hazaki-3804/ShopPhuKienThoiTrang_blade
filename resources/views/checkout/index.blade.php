@extends('layouts.app')
@section('title', 'Thanh toán')

@section('content')
<div class="container py-4">
    <h5 class="fw-semibold mb-3">Thanh toán</h5>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form class="row g-3" method="POST" action="{{ route('checkout.saveAddress') }}">
                        @csrf
                        @foreach(($selected ?? []) as $pid)
                        <input type="hidden" name="selected[]" value="{{ $pid }}">
                        @endforeach
                        <div class="col-12 col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <select id="checkout_province" class="form-select" data-placeholder="-- Chọn tỉnh thành --">
                                        <option value="" disabled selected>-- Chọn tỉnh --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 mt-2 mt-md-0">
                                    <select id="checkout_ward" class="form-select" data-placeholder="-- Chọn xã/phường --">
                                        <option value="" disabled selected>-- Chọn xã/phường --</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden fields to persist selected names to server-side -->
                            <input type="hidden" name="province_name" id="province_name">
                            <input type="hidden" name="ward_name" id="ward_name">
                            <div class="input-group mt-2">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <textarea name="customer_address" class="form-control" rows="2" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." required>{{ old('customer_address') }}</textarea>
                            </div>
                        </div>
                        <div class="col-12 d-flex align-items-end">
                            <button class="btn btn-brand w-100">Lưu địa chỉ</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="fw-semibold">Tóm tắt đơn</h6>
                    @foreach(($items ?? []) as $line)
                    @php
                    $p = $line['product'];
                    $img = optional($p->product_images[0] ?? null)->image_url ?? null;
                    if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) { $img = asset($img); }
                    $img = $img ?: 'https://picsum.photos/80/80?random=' . ($p->id ?? 1);
                    @endphp
                    <div class="d-flex align-items-center justify-content-between small mb-2">
                        <div class="d-flex align-items-center gap-2 me-2">
                            <img src="{{ $img }}" alt="{{ $p->name }}" class="checkout-thumb rounded border">
                            <div class="lh-sm">
                                <div class="fw-semibold text-truncate checkout-name" title="{{ $p->name }}">{{ $p->name }}</div>
                                <div class="text-muted">x {{ $line['qty'] }}</div>
                            </div>
                        </div>
                        <span class="ms-2">{{ number_format($line['subtotal'],0,',','.') }}₫</span>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-between mt-2"><strong>Tổng</strong><strong>{{ number_format($total ?? 0,0,',','.') }}₫</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
<style>
    .checkout-thumb {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    .checkout-name {
        max-width: 210px;
    }

    @media (min-width: 992px) {
        .checkout-name {
            max-width: 240px;
        }
    }
</style>
@endpush
@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Match register page look */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border-radius: .375rem;
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .select2-container .select2-selection--single {
        padding-left: .5rem;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-results__option {
        font-size: 14px;
    }

    .select2-selection__clear {
        margin-right: 6px;
    }

    label.form-label+.row .select2-container {
        margin-top: .25rem;
    }

    /* Input group icons styling */
    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
        color: #6c757d;
        min-width: 45px;
        justify-content: center;
    }

    .input-group-text i {
        font-size: 1.1em;
    }

    /* Special styling for textarea with icon */
    .input-group .form-control:not(:first-child) {
        border-left: 0;
    }

    .input-group .input-group-text:not(:last-child) {
        border-right: 0;
    }
</style>
@endpush
@push('scripts')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const $prov = $('#checkout_province');
        const $ward = $('#checkout_ward');
        // init select2
        $prov.select2({
            placeholder: '-- Chọn tỉnh thành --',
            allowClear: true,
            width: '100%'
        });
        $ward.select2({
            placeholder: '-- Chọn xã/phường --',
            allowClear: true,
            width: '100%'
        });

        // load provinces
        fetch('https://provinces.open-api.vn/api/v2/?depth=2')
            .then(r => r.json())
            .then(data => {
                data.forEach(p => {
                    $('#checkout_province').append(`<option value="${p.code}">${p.name}</option>`);
                });
                $prov.trigger('change.select2');
                // cache data for wards lookup
                window.__VN_PROVINCES__ = data;
            }).catch(err => console.error('Load provinces failed', err));

        $prov.on('change', function() {
            const code = $(this).val();
            $ward.empty().append('<option value=""></option>').trigger('change.select2');
            const data = (window.__VN_PROVINCES__ || []).find(p => String(p.code) === String(code));
            if (data) {
                data.wards.forEach(w => {
                    $ward.append(`<option value="${w.code}">${w.name}</option>`);
                });
                $ward.trigger('change.select2');
            }
            // set hidden province_name
            const provText = $prov.find('option:selected').text();
            document.getElementById('province_name').value = provText && provText.indexOf('Chọn') === -1 ? provText.trim() : '';
        });

        $ward.on('change', function() {
            const wardText = $ward.find('option:selected').text();
            document.getElementById('ward_name').value = wardText && wardText.indexOf('Chọn') === -1 ? wardText.trim() : '';
        });

        // Merge selected province/ward text into address on submit
        const form = document.querySelector('form[action="{{ route('checkout.saveAddress') }}"]');
        if (form) {
            form.addEventListener('submit', function() {
                const addrEl = form.querySelector('textarea[name="customer_address"]');
                const provText = document.getElementById('province_name').value || $prov.find('option:selected').text();
                const wardText = document.getElementById('ward_name').value || $ward.find('option:selected').text();
                const parts = [];
                if (addrEl && addrEl.value) parts.push(addrEl.value.trim());
                if (wardText && wardText.indexOf('Chọn') === -1) parts.push(wardText.trim());
                if (provText && provText.indexOf('Chọn') === -1) parts.push(provText.trim());
                if (addrEl) addrEl.value = parts.join(', ');
            });
        }
    });
</script>
@endpush
@endsection