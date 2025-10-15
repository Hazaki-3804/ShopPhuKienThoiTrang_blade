@push('styles')
<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
@endpush
<div class="position-fixed" style="z-index: 2000; right: 20px !important; left: auto !important; bottom: 20px !important;">
    <button id="toggle-chat" class="btn rounded-circle shadow-lg" style="width: 60px; height: 60px; font-size: 24px; background-color:#ff6f3c; border:none; color:#fff;">
        💬
    </button>

    <div id="chat-box" class="card shadow-lg mt-3" style="width: 50vh; height: 80vh; max-height: 60vh; display: none; flex-direction: column;">
        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color:#ff6f3c;">
            <span>Nàng Thơ Assitant</span>
            <button id="close-chat" class="btn btn-lg">&times;</button>
        </div>

        <div id="chat-container" class="card-body overflow-auto" style="flex: 1; padding: 12px;"></div>

        <div class="card-footer d-flex gap-2 align-items-center">
            <button id="upload-file" class="btn btn-outline-secondary btn-sm" title="Gửi ảnh"><i class="bi bi-image"></i></button>
            <input type="text" id="chat-input" class="form-control form-control-sm" placeholder="Nhập tin nhắn...">
            <input type="file" id="chat-file" class="d-none" accept="image/*">
            <button id="send-message" class="btn btn-sm" style="background-color:#ff6f3c; color:#fff;">➤</button>
        </div>
    </div>
</div>
@push('scripts')
<script src="{{ asset('js/chatbot.js') }}"></script>
@endpush