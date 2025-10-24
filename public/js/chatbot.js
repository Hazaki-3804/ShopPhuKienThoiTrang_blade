
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggle-chat');
    const closeBtn = document.getElementById('close-chat');
    const chatBox = document.getElementById('chat-box');
    const chatContainer = document.getElementById('chat-container');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-message');
    const uploadBtn = document.getElementById('upload-file');
    const fileInput = document.getElementById('chat-file');

    let chatHistory = [{
        role: 'bot',
        content: 'Chào bạn, mình là trợ lý ảo của Nàng Thơ Shop. Mình rất vui có thể tìm kiếm thông tin cho bạn!. Bạn có cần hỗ trợ gì không?'
    }];
    let chatLoading = false;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const renderChat = () => {
        chatContainer.innerHTML = '';
        chatHistory.forEach(msg => {
            const rowEl = document.createElement('div');
            rowEl.className = msg.role === 'user' ? 'chat-row user' : 'chat-row bot';

            const msgEl = document.createElement('div');
            msgEl.className = msg.role === 'user' ? 'chat-bubble user' : 'chat-bubble bot';

            // Strip most emojis from bot messages
            const stripEmojis = (text) => {
                if (!text) return text;
                try {
                    return text.replace(/[\u{1F300}-\u{1FAFF}\u{1F900}-\u{1F9FF}\u{2600}-\u{27BF}\u{FE0F}]/gu, '');
                } catch (e) {
                    // Fallback without unicode flag if unsupported
                    return text.replace(/[\u2600-\u27BF]/g, '').replace(/[\uFE0F]/g, '');
                }
            };

            // Lightweight markdown renderer for better presentation
            const renderMarkdown = (text) => {
                if (!text) return '';
                let t = text;
                // Normalize line endings
                t = t.replace(/\r\n/g, '\n');
                // Bold **text**
                t = t.replace(/\*\*(.+?)\*\*/g, '<strong>$1<\/strong>');
                // Headings: line starting with '# '
                t = t.replace(/^#\s+(.*)$/gm, '<div class="md-h1">$1<\/div>');
                t = t.replace(/^##\s+(.*)$/gm, '<div class="md-h2">$1<\/div>');
                // Bullet lists: lines starting with * or -
                if (/^[*-]\s+/m.test(t)) {
                    t = t.replace(/^(?:\s*[*-]\s+.*(?:\n|$))+?/gm, (block) => {
                        const items = block.trim().split(/\n+/).map(l => l.replace(/^[*-]\s+/, '').trim()).filter(Boolean);
                        return '<ul class="md-list">' + items.map(i => `<li>${i}<\/li>`).join('') + '<\/ul>';
                    });
                }
                // Convert paragraphs: split by double newline
                t = t.split(/\n{2,}/).map(p => {
                    // keep existing HTML blocks
                    if (/^<\/?(ul|div|p|strong)/.test(p.trim())) return p;
                    return '<p>' + p.replace(/\n/g, '<br>') + '<\/p>';
                }).join('');
                return t;
            };

            let raw = msg.role === 'bot' ? stripEmojis(msg.content) : msg.content;
            let contentHtml = msg.role === 'bot' ? renderMarkdown(raw) : raw.replace(/\n/g, '<br>');
            if (msg.image) contentHtml += `<br><img src="${msg.image}" class="chat-img">`;
            if (msg.links) contentHtml += Object.entries(msg.links).map(([key, url]) => `
                <a href="${url}" target="_blank" class="badge text-white mt-1 me-1" style="background-color:#ff6f3c;">
                    ${key.charAt(0).toUpperCase() + key.slice(1)}
                </a>`).join('');
            msgEl.innerHTML = contentHtml;

            // Mark long bot messages as rich for elevated styling
            if (msg.role === 'bot' && (raw && raw.length > 120)) {
                msgEl.classList.add('rich');
            }

            rowEl.appendChild(msgEl);
            chatContainer.appendChild(rowEl);
        });
        if (chatLoading) {
            const loadingEl = document.createElement('div');
            loadingEl.className = 'text-center text-muted';
            loadingEl.textContent = '...';
            chatContainer.appendChild(loadingEl);
        }
        chatContainer.scrollTop = chatContainer.scrollHeight;
    };

    const sendMessage = async () => {
        const message = chatInput.value.trim();
        if (!message) return;
        chatHistory.push({
            role: 'user',
            content: message
        });
        chatInput.value = '';
        chatLoading = true;
        renderChat();

        try {
            const res = await fetch('/api/chatbot', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    message
                })
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            chatHistory.push({
                role: 'bot',
                content: data.message,
                links: data.links || {}
            });
        } catch (e) {
            chatHistory.push({
                role: 'bot',
                content: 'Có lỗi xảy ra, thử lại sau.'
            });
            console.error(e);
        } finally {
            chatLoading = false;
            renderChat();
        }
    };

    const sendFile = async (file) => {
        if (!file) return;
        chatHistory.push({
            role: 'user',
            content: '',
            image: URL.createObjectURL(file)
        });
        chatLoading = true;
        renderChat();

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
            chatHistory.push({
                role: 'bot',
                content: data.message,
                links: data.links || {}
            });
        } catch (e) {
            chatHistory.push({
                role: 'bot',
                content: 'Có lỗi xảy ra khi gửi ảnh.'
            });
            console.error(e);
        } finally {
            chatLoading = false;
            renderChat();
        }
    };

    toggleBtn.addEventListener('click', () => chatBox.style.display = chatBox.style.display === 'flex' ? 'none' : 'flex');
    closeBtn.addEventListener('click', () => chatBox.style.display = 'none');
    sendBtn.addEventListener('click', sendMessage);
    chatInput.addEventListener('keyup', e => {
        if (e.key === 'Enter') sendMessage();
    });
    uploadBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) sendFile(fileInput.files[0]);
        fileInput.value = '';
    });

    renderChat();
});
