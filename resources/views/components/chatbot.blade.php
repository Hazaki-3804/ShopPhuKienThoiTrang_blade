@push('styles')
<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
@endpush

<!-- Chatbot Container -->
<div class="chatbot-wrapper position-fixed" style="z-index: 2000; right: 20px; bottom: 20px;">
    <!-- Toggle Button with Notification Badge -->
    <button id="toggle-chat" class="chatbot-toggle-btn  pulse-heart" aria-label="Open Chat">
        <svg class="chat-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span class="notification-badge" id="notification-badge" style="display: none;">1</span>
    </button>

    <!-- Chat Box -->
    <div id="chat-box" class="chatbot-box" style="display: none;">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="d-flex align-items-center gap-3">
                <div class="chatbot-avatar">
                    <img src="{{ asset('img/chatbot-avatar.png') }}" alt="Mia" onerror="this.src='https://ui-avatars.com/api/?name=Mia&background=ff6f3c&color=fff&size=128'">
                    <span class="status-indicator"></span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">Mia - Trợ lý ảo</h6>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button id="minimize-chat" class="chatbot-control-btn d-none" title="Thu nhỏ">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </button>
                <button id="close-chat" class="chatbot-control-btn" title="Đóng">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Messages Container -->
        <div id="chat-container" class="chatbot-messages"></div>

        <!-- Quick Replies -->
        <div id="quick-replies" class="chatbot-quick-replies" style="display: none;">
            <button class="quick-reply-btn" data-message="Xem sản phẩm mới nhất">🎀 Sản phẩm mới</button>
            <button class="quick-reply-btn" data-message="Phí ship là bao nhiêu?">🚚 Phí ship</button>
            <button class="quick-reply-btn" data-message="Có khuyến mãi gì không?">🎉 Khuyến mãi</button>
            <button class="quick-reply-btn" data-message="Tư vấn sản phẩm">💬 Tư vấn</button>
        </div>

        <!-- Input Area -->
        <div class="chatbot-input-area">
            <div class="input-wrapper">
                <button id="upload-file" class="input-action-btn" title="Gửi ảnh">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                        <polyline points="21 15 16 10 5 21"></polyline>
                    </svg>
                </button>
                <input type="text" id="chat-input" class="chatbot-input" placeholder="Nhập tin nhắn..." autocomplete="off">
                <input type="file" id="chat-file" class="d-none" accept="image/*">
                <button id="send-message" class="send-btn" title="Gửi">
                    <svg class="send-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Powered by -->
        <div class="chatbot-footer">
            <small class="text-muted">Powered by Shop Nàng Thơ✨</small>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/chatbot.js') }}"></script>
@endpush