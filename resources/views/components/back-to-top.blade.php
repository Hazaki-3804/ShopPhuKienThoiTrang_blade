<!-- scroll-to-top.blade.php -->
<button
    id="scrollToTopBtn"
    class="position-fixed d-none rounded-circle text-white"
    style="left: 30px; right: auto; bottom: 30px; width: 50px; height: 50px; background-color: #FF5722; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 2100; font-size: 1.5rem; border: none; cursor: pointer;">
    <i class="bi bi-arrow-up"></i>
</button>
@push('scripts')
<script src="{{ asset('js/back-to-top.js') }}"></script>
@endpush