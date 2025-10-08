@props([
    'name' => 'qty',
    'value' => 1,
    'min' => 1,
    'max' => null,
    'id' => null,
    // autoSubmit: if true, submit the closest form on change
    'autoSubmit' => false,
    'disabled' => false,
    'inputClass' => '',
])
@php
  $inputId = $id ?? ('qty_'.uniqid());
@endphp
<div class="qty-stepper d-inline-flex align-items-center">
  <button type="button" class="btn btn-stepper" data-action="decrease" {{ $disabled ? 'disabled' : '' }}>−</button>
  <input
    type="number"
    id="{{ $inputId }}"
    name="{{ $name }}"
    value="{{ $value }}"
    min="{{ $min }}"
    {{ $max ? 'max='.$max : '' }}
    class="form-control text-center qty-input {{ $inputClass }}"
    {{ $disabled ? 'disabled' : '' }}
  >
  <button type="button" class="btn btn-stepper" data-action="increase" {{ $disabled ? 'disabled' : '' }}>+</button>
</div>

@push('styles')
<style>
  /* Compact group wrapper to mimic a single bordered control */
  .qty-stepper {
    border: 1px solid #dcdcdc;
    border-radius: 4px;
    overflow: hidden;
  }
  .btn-stepper {
    background: #fff;
    border: 0;
    line-height: 1;
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    user-select: none;
  }
  .btn-stepper:hover { background: #f7f7f7; }
  .qty-stepper .form-control {
    height: 28px;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    padding: 0;
    width: 48px; /* center number, compact like screenshot */
    font-size: 0.95rem;
  }
  /* Hide default number spinners (we use +/- buttons instead) */
  .qty-stepper input.qty-input::-webkit-outer-spin-button,
  .qty-stepper input.qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }
  .qty-stepper input.qty-input { -moz-appearance: textfield; }
</style>
@endpush

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.qty-stepper').forEach(function(box){
      const input = box.querySelector('input.qty-input');
      const dec = box.querySelector('[data-action="decrease"]');
      const inc = box.querySelector('[data-action="increase"]');
      if (!input) return;
      function clamp(val){
        let v = parseInt(val || 0, 10);
        const min = parseInt(input.min || '1', 10);
        const max = input.max ? parseInt(input.max, 10) : null;
        if (isNaN(v) || v < min) v = min;
        if (max && v > max) v = max;
        return v;
      }
      function triggerChange(){
        const evt = new Event('change', { bubbles: true });
        input.dispatchEvent(evt);
      }
      if (dec) dec.addEventListener('click', function(){
        input.value = clamp((parseInt(input.value||'0',10) - 1));
        triggerChange();
        @if($autoSubmit)
          const form = input.closest('form');
          if (form) form.submit();
        @endif
      });
      if (inc) inc.addEventListener('click', function(){
        input.value = clamp((parseInt(input.value||'0',10) + 1));
        triggerChange();
        @if($autoSubmit)
          const form = input.closest('form');
          if (form) form.submit();
        @endif
      });
      input.addEventListener('blur', function(){
        input.value = clamp(input.value);
      });
    });
  });
</script>
@endpush
