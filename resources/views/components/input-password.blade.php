@props([
'name' => 'password',
'autocomplete' => 'current-password',
])
<div class="mb-3">
    <div class="position-relative">
        <input
            type="password"
            name="{{ $name }}"
            id="{{ $name }}"
            autocomplete="{{ $autocomplete }}"
            class="form-control pr-5">
        <button type="button"
            class="btn position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent toggle-password"
            data-target="{{ $name }}">
            <i class="bi bi-eye"></i>
        </button>
    </div>

</div>

@once
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".toggle-password").forEach(function(btn) {
            btn.addEventListener("click", function() {
                const targetId = this.getAttribute("data-target");
                const input = document.getElementById(targetId);
                const icon = this.querySelector("i");

                if (input.type === "password") {
                    input.type = "text";
                    icon.classList.remove("bi-eye");
                    icon.classList.add("bi-eye-slash");
                } else {
                    input.type = "password";
                    icon.classList.remove("bi-eye-slash");
                    icon.classList.add("bi-eye");
                }
            });
        });
    });
</script>
@endpush
@endonce