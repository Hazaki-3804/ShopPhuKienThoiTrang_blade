@props([
    'table' => null, // DataTable instance variable name
    'forms' => [] // Array of form selectors to handle
])

<!-- Include AJAX Form Handler JavaScript -->
<script src="{{ asset('js/admin/ajax-form-handler.js') }}"></script>

<script>
$(document).ready(function() {
    // Initialize AJAX Form Handler with passed options
    @if($table || !empty($forms))
    AjaxFormHandler.init({
        @if($table)
        table: '{{ $table }}',
        @endif
        @if(!empty($forms))
        forms: @json($forms)
        @endif
    });
    @endif
});
</script>
