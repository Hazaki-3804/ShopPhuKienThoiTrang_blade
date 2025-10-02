<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
    @foreach (['success','error','info','warning'] as $type)
    @if (session($type))
    @php
    $messages = (array) session($type);
    $bgClass = match($type) {
    'success' => 'toast-success-light',
    'error' => 'toast-error-light',
    'info' => 'toast-info-light',
    'warning' => 'toast-warning-light',
    };
    $icon = match($type) {
    'success' => '✅',
    'error' => '❌',
    'info' => 'ℹ️',
    'warning' => '⚠️',
    };
    $delay = match($type) {
    'success' => 2500,
    'info' => 3000,
    'warning' => 3500,
    'error' => 4000,
    };
    @endphp
    @foreach ($messages as $msg)
    <div class="toast align-items-center {{ $bgClass }} p-2 border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="{{ $delay }}">
        <div class="d-flex">
            <div class="toast-body">
                <span class="me-2">{{ $icon }}</span> {{ $msg }}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
    @endforeach
    @endif
    @endforeach
</div>