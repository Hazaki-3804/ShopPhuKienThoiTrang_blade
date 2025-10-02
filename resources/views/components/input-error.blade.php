@props(['message'])
@if($message)
<div class="text-danger mt-1" style="font-size: 12px;">
    <strong><i class="bi bi-exclamation-circle-fill"></i> {{ $message }}</strong>
</div>
@endif