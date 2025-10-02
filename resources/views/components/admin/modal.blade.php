@props([
'title' => '',
'modal_id' => '',
'url' => '',
'button_type' => 'Lưu'
])

<div {{ $attributes->merge(['class'=>'modal fade']) }}
    id="{{ $modal_id }}"
    tabindex="-1"
    role="dialog"
    aria-labelledby="{{ $modal_id }}Label"
    aria-hidden="true"
    data-backdrop="static"
    data-keyboard="false">

    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modal_id }}Label">{{ $title }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Form -->
            <form action="{{ $url }}" method="POST">
                @csrf
                <div class="modal-body">
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ $button_type }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>