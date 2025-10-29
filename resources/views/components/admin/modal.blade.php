@props([
'title' => '',
'modal_id' => '',
'url' => '',
'button_type' => 'Lưu',
'method' => 'POST'
])

<div {{ $attributes->merge(['class'=>'modal fade']) }}
    id="{{ $modal_id }}"
    tabindex="-1"
    role="dialog"
    aria-labelledby="{{ $modal_id }}Label"
    aria-hidden="true"
    data-backdrop="static"
    data-keyboard="false">

    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
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
                @if(strtoupper($method) !== 'POST')
                @method($method)
                @endif
                <div class="modal-body" style="max-height: calc(100vh - 210px); overflow-y: auto;">
                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    @if($button_type === 'delete')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                    @elseif($button_type === 'update')
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                    @else
                    <button type="submit" class="btn btn-primary">Lưu</button>
                    @endif
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</div>