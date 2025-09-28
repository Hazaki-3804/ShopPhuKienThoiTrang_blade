@props([
'id' => 'qtyInput',
'name' => 'qty',
'value' => 1,
'min' => 1,
'max' => 99,
'size' => 'sm', {{-- sm, md, lg --}}
])

<div class="input-group input-group-{{ $size }}" style="width: 100px;">
    <button class="btn btn-outline-secondary" type="button" onclick="changeQty('{{ $id }}', -1)">
        <i class="bi bi-dash"></i>
    </button>
    <input type="text"
        id="{{ $id }}"
        name="{{ $name }}"
        class="form-control text-center w-4"
        value="{{ $value }}"
        min="{{ $min }}"
        max="{{ $max }}">
    <button class="btn btn-outline-secondary" type="button" onclick="changeQty('{{ $id }}', 1)">
        <i class="bi bi-plus"></i>
    </button>
</div>
@push('scripts')
<script src="{{ asset('js/quanlity-selector.js') }}"></script>
@endpush