/**
 * Modern Chatbot JavaScript
 * Features: Typing indicator, Quick replies, Smooth animations, Timestamps
 */

document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements
    const toggleBtn = document.getElementById('toggle-chat');
    const closeBtn = document.getElementById('close-chat');
    const minimizeBtn = document.getElementById('minimize-chat');
    const chatBox = document.getElementById('chat-box');
    const chatContainer = document.getElementById('chat-container');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-message');
    const uploadBtn = document.getElementById('upload-file');
    const fileInput = document.getElementById('chat-file');
    const quickRepliesContainer = document.getElementById('quick-replies');
    const notificationBadge = document.getElementById('notification-badge');

    // State
    let chatHistory = [];
    let isTyping = false;
    let isFirstOpen = true;

    // CSRF Token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ========================================
    // UTILITY FUNCTIONS
    // ========================================

    /**
     * Get current timestamp
     */
    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    }

    /**
     * Strip emojis from text
     */
    function stripEmojis(text) {
        if (!text) return text;
        try {
            return text.replace(/[\u{1F300}-\u{1FAFF}\u{1F900}-\u{1F9FF}\u{2600}-\u{27BF}\u{FE0F}]/gu, '');
        } catch (e) {
            return text.replace(/[\u2600-\u27BF]/g, '').replace(/[\uFE0F]/g, '');
        }
    }

    /**
     * Lightweight markdown renderer
     */
    function renderMarkdown(text) {
        if (!text) return '';
        let t = text;
        
        // Normalize line endings
        t = t.replace(/\r\n/g, '\n');
        
        // Bold **text**
        t = t.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        
        // Headings
        t = t.replace(/^#\s+(.*)$/gm, '<div class="md-h1">$1</div>');
        t = t.replace(/^##\s+(.*)$/gm, '<div class="md-h2">$1</div>');
        
        // Bullet lists
        if (/^[*•-]\s+/m.test(t)) {
            t = t.replace(/^(?:\s*[*•-]\s+.*(?:\n|$))+?/gm, (block) => {
                const items = block.trim().split(/\n+/)
                    .map(l => l.replace(/^[*•-]\s+/, '').trim())
                    .filter(Boolean);
                return '<ul class="md-list">' + items.map(i => `<li>${i}</li>`).join('') + '</ul>';
            });
        }
        
        // Convert paragraphs
        t = t.split(/\n{2,}/).map(p => {
            if (/^<\/?(ul|div|p|strong)/.test(p.trim())) return p;
            return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
        }).join('');
        
        return t;
    }

    // ========================================
    // RENDER FUNCTIONS
    // ========================================

    /**
     * Render typing indicator
     */
    function showTypingIndicator() {
        const typingEl = document.createElement('div');
        typingEl.className = 'chat-row bot';
        typingEl.id = 'typing-indicator';
        
        const bubble = document.createElement('div');
        bubble.className = 'typing-indicator';
        bubble.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        
        typingEl.appendChild(bubble);
        chatContainer.appendChild(typingEl);
        scrollToBottom();
    }

    /**
     * Remove typing indicator
     */
    function hideTypingIndicator() {
        const typingEl = document.getElementById('typing-indicator');
        if (typingEl) {
            typingEl.remove();
        }
    }

    /**
     * Render chat messages
     */
    function renderChat() {
        chatContainer.innerHTML = '';
        
        chatHistory.forEach(msg => {
            const rowEl = document.createElement('div');
            rowEl.className = msg.role === 'user' ? 'chat-row user' : 'chat-row bot';

            const msgEl = document.createElement('div');
            msgEl.className = msg.role === 'user' ? 'chat-bubble user' : 'chat-bubble bot';

            // Process content
            let raw = msg.role === 'bot' ? stripEmojis(msg.content) : msg.content;
            let contentHtml = msg.role === 'bot' ? renderMarkdown(raw) : raw.replace(/\n/g, '<br>');
            
            // Add image if exists
            if (msg.image) {
                contentHtml += `<br><img src="${msg.image}" class="chat-img">`;
            }
            
            // Add links if exists
            if (msg.links && Object.keys(msg.links).length > 0) {
                contentHtml += '<div class="mt-2">';
                contentHtml += Object.entries(msg.links).map(([key, url]) => `
                    <a href="${url}" target="_blank" class="badge">
                        ${key.charAt(0).toUpperCase() + key.slice(1)}
                    </a>
                `).join('');
                contentHtml += '</div>';
            }
            
            msgEl.innerHTML = contentHtml;

            // Add rich class for long bot messages
            if (msg.role === 'bot' && raw && raw.length > 120) {
                msgEl.classList.add('rich');
            }

            // Add timestamp
            if (msg.timestamp) {
                const timeEl = document.createElement('div');
                timeEl.className = 'chat-timestamp';
                timeEl.textContent = msg.timestamp;
                msgEl.appendChild(timeEl);
            }

            rowEl.appendChild(msgEl);
            chatContainer.appendChild(rowEl);
        });

        scrollToBottom();
    }

    /**
     * Scroll to bottom smoothly
     */
    function scrollToBottom() {
        setTimeout(() => {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }, 100);
    }

    /**
     * Show quick replies
     */
    function showQuickReplies() {
        quickRepliesContainer.style.display = 'flex';
    }

    /**
     * Hide quick replies
     */
    function hideQuickReplies() {
        quickRepliesContainer.style.display = 'none';
    }

    // ========================================
    // MESSAGE FUNCTIONS
    // ========================================

    /**
     * Send text message
     */
    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message || isTyping) return;

        // Add user message
        chatHistory.push({
            role: 'user',
            content: message,
            timestamp: getCurrentTime()
        });
        
        chatInput.value = '';
        hideQuickReplies();
        renderChat();

        // Show typing indicator
        isTyping = true;
        showTypingIndicator();

        try {
            const res = await fetch('/api/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ message })
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            
            const data = await res.json();
            
            // Simulate typing delay for better UX
            await new Promise(resolve => setTimeout(resolve, 800));
            
            hideTypingIndicator();
            
            chatHistory.push({
                role: 'bot',
                content: data.message,
                links: data.links || {},
                timestamp: getCurrentTime()
            });
            
        } catch (e) {
            hideTypingIndicator();
            chatHistory.push({
                role: 'bot',
                content: 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại sau! 😔',
                timestamp: getCurrentTime()
            });
            console.error('Chatbot error:', e);
        } finally {
            isTyping = false;
            renderChat();
        }
    }

    /**
     * Send file
     */
    async function sendFile(file) {
        if (!file || isTyping) return;

        chatHistory.push({
            role: 'user',
            content: '📷 Đã gửi hình ảnh',
            image: URL.createObjectURL(file),
            timestamp: getCurrentTime()
        });
        
        hideQuickReplies();
        renderChat();

        isTyping = true;
        showTypingIndicator();

        const formData = new FormData();
        formData.append('file', file);

        try {
            const res = await fetch('/api/chatbot/file', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            
            const data = await res.json();
            
            await new Promise(resolve => setTimeout(resolve, 800));
            
            hideTypingIndicator();
            
            chatHistory.push({
                role: 'bot',
                content: data.message,
                links: data.links || {},
                timestamp: getCurrentTime()
            });
            
        } catch (e) {
            hideTypingIndicator();
            chatHistory.push({
                role: 'bot',
                content: 'Có lỗi xảy ra khi xử lý ảnh. Vui lòng thử lại!',
                timestamp: getCurrentTime()
            });
            console.error('File upload error:', e);
        } finally {
            isTyping = false;
            renderChat();
        }
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================

    // Toggle chat
    toggleBtn.addEventListener('click', () => {
        const isVisible = chatBox.style.display === 'flex';
        
        if (isVisible) {
            chatBox.style.display = 'none';
        } else {
            chatBox.style.display = 'flex';
            chatInput.focus();
            
            // Show default greeting on first open
            if (isFirstOpen && chatHistory.length === 0) {
                chatHistory.push({
                    role: 'bot',
                    content: 'Xin chào! Mình là **Mia** - trợ lý ảo của shop. 👋\n\nMình có thể giúp bạn:\n\n• Tìm kiếm sản phẩm\n• Tư vấn phụ kiện phù hợp\n• Kiểm tra giá và tồn kho\n• Thông tin về giao hàng và thanh toán\n• Theo dõi đơn hàng\n\nBạn cần mình hỗ trợ gì nào? 😊',
                    timestamp: getCurrentTime()
                });
                renderChat();
                showQuickReplies();
                isFirstOpen = false;
            }
            
            // Hide notification badge
            notificationBadge.style.display = 'none';
        }
    });

    // Close chat
    closeBtn.addEventListener('click', () => {
        chatBox.style.display = 'none';
    });

    // Minimize chat
    minimizeBtn.addEventListener('click', () => {
        chatBox.style.display = 'none';
    });

    // Send message on button click
    sendBtn.addEventListener('click', sendMessage);

    // Send message on Enter key
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Upload file button
    uploadBtn.addEventListener('click', () => {
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            sendFile(fileInput.files[0]);
            fileInput.value = '';
        }
    });

    // Quick reply buttons
    document.querySelectorAll('.quick-reply-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const message = btn.getAttribute('data-message');
            chatInput.value = message;
            sendMessage();
        });
    });

    // Auto-resize input on type
    chatInput.addEventListener('input', () => {
        // Enable/disable send button based on input
        if (chatInput.value.trim()) {
            sendBtn.style.opacity = '1';
        } else {
            sendBtn.style.opacity = '0.5';
        }
    });

    // ========================================
    // INITIALIZATION
    // ========================================

    // Set initial send button state
    sendBtn.style.opacity = '0.5';

    // Focus input when chat opens
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target.style.display === 'flex') {
                setTimeout(() => chatInput.focus(), 100);
            }
        });
    });

    observer.observe(chatBox, {
        attributes: true,
        attributeFilter: ['style']
    });

    console.log('🤖 Chatbot initialized successfully!');
});
