@extends('adminlte::page')

@section('title', 'AI Asisten Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="m-0 text-dark">
            <i class="fas fa-robot mr-2 text-primary"></i>
            AI Asisten Dashboard
        </h1>
        <small class="text-muted mt-2 mt-md-0">Tanya fitur, menu, dan cara penggunaan sesuai role Anda</small>
    </div>
@stop

@section('content')
    <style>
        .assistant-shell {
            max-width: 980px;
            margin: 0 auto;
        }

        .assistant-chat {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            background: #ffffff;
            min-height: 460px;
            max-height: 62vh;
            overflow-y: auto;
            padding: 16px;
        }

        .msg-row {
            display: flex;
            margin-bottom: 12px;
        }

        .msg-row.user {
            justify-content: flex-end;
        }

        .msg-row.assistant {
            justify-content: flex-start;
        }

        .msg-bubble {
            max-width: 78%;
            border-radius: 14px;
            padding: 10px 12px;
            line-height: 1.45;
            font-size: 14px;
            white-space: pre-wrap;
        }

        .msg-row.assistant .msg-bubble {
            white-space: normal;
        }

        .msg-bubble .ai-md-p {
            margin: 0 0 8px;
        }

        .msg-bubble .ai-md-p:last-child {
            margin-bottom: 0;
        }

        .msg-bubble .ai-md-list {
            margin: 0 0 8px 18px;
            padding: 0;
        }

        .msg-bubble .ai-md-list:last-child {
            margin-bottom: 0;
        }

        .msg-bubble code {
            background: #e2e8f0;
            border-radius: 6px;
            padding: 1px 5px;
            font-size: 12px;
        }

        .msg-bubble .ai-md-h1,
        .msg-bubble .ai-md-h2,
        .msg-bubble .ai-md-h3 {
            margin: 0 0 8px;
            line-height: 1.35;
            color: #0f172a;
        }

        .msg-bubble .ai-md-h1 {
            font-size: 18px;
            font-weight: 700;
        }

        .msg-bubble .ai-md-h2 {
            font-size: 16px;
            font-weight: 700;
        }

        .msg-bubble .ai-md-h3 {
            font-size: 15px;
            font-weight: 600;
        }

        .msg-bubble .ai-md-link {
            color: #0b63ce;
            text-decoration: underline;
            word-break: break-all;
        }

        .msg-row.user .msg-bubble {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: #fff;
            border-bottom-right-radius: 6px;
        }

        .msg-row.assistant .msg-bubble {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 6px;
        }

        .msg-meta {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        .assistant-input-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #fff;
            padding: 12px;
        }

        .assistant-hint {
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            background: #e0f2fe;
            color: #0c4a6e;
            border: 1px solid #bae6fd;
            cursor: pointer;
        }

        .assistant-hint:hover {
            background: #bae6fd;
        }
    </style>

    <div class="assistant-shell">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold mb-0">Asisten Cerdas MS Publishing</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" class="assistant-hint" data-hint="Fitur apa saja di dashboard saya?">Fitur
                        dashboard saya</button>
                    <button type="button" class="assistant-hint" data-hint="Cara menggunakan hitung halaman naskah">Cara
                        hitung halaman</button>
                    <button type="button" class="assistant-hint" data-hint="Cara cek invoice dan status pembayaran">Cek
                        invoice</button>
                    <button type="button" class="assistant-hint" data-hint="Cara upload file di Ruang File Role">Ruang File
                        Role</button>
                    <a href="{{ route('assistant.export') }}" class="assistant-hint text-decoration-none"
                        title="Export riwayat chat ke CSV">
                        Export CSV
                    </a>
                </div>

                <div id="assistant-chat" class="assistant-chat mb-3">
                    <div class="msg-row assistant">
                        <div>
                            <div class="msg-bubble">Halo, saya AI Asisten. Silakan tanya apa pun tentang fitur dan cara
                                penggunaan dashboard Anda.</div>
                            <div class="msg-meta">Asisten</div>
                        </div>
                    </div>
                </div>

                <form id="assistant-form" class="assistant-input-wrap">
                    <div class="form-group mb-2">
                        <label for="assistant-question" class="mb-1">Pertanyaan Anda</label>
                        <textarea id="assistant-question" class="form-control" rows="3" maxlength="3000"
                            placeholder="Contoh: Saya role author, cara cek invoice paket dan status pembayarannya bagaimana?"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <small class="text-muted mb-2 mb-md-0">Tips: sebutkan role atau menu agar jawaban lebih
                            akurat.</small>
                        <button id="assistant-submit" type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i>Kirim Pertanyaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        (function() {
            const form = document.getElementById('assistant-form');
            const questionInput = document.getElementById('assistant-question');
            const submitBtn = document.getElementById('assistant-submit');
            const chat = document.getElementById('assistant-chat');
            const hintButtons = Array.from(document.querySelectorAll('[data-hint]'));
            let nextBeforeId = null;
            let hasMoreHistory = true;
            let isLoadingHistory = false;
            let shouldStickToBottom = true;

            const isNearBottom = () => {
                const threshold = 60;
                return (chat.scrollHeight - chat.scrollTop - chat.clientHeight) <= threshold;
            };

            const scrollToBottom = () => {
                chat.scrollTop = chat.scrollHeight;
            };

            const escapeHtml = (value) => String(value || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');

            const formatInline = (value) => value
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/`([^`]+)`/g, '<code>$1</code>')
                .replace(/(https?:\/\/[^\s<]+)/g,
                    '<a class="ai-md-link" href="$1" target="_blank" rel="noopener noreferrer">$1</a>');

            const formatAssistantText = (value) => {
                const lines = escapeHtml(value).replace(/\r\n/g, '\n').split('\n');
                const out = [];
                let inUl = false;
                let inOl = false;

                const closeLists = () => {
                    if (inUl) {
                        out.push('</ul>');
                        inUl = false;
                    }
                    if (inOl) {
                        out.push('</ol>');
                        inOl = false;
                    }
                };

                for (const rawLine of lines) {
                    const line = rawLine.trim();

                    if (line === '') {
                        closeLists();
                        out.push('<br>');
                        continue;
                    }

                    const ulMatch = line.match(/^[-*]\s+(.+)$/);
                    if (ulMatch) {
                        if (inOl) {
                            out.push('</ol>');
                            inOl = false;
                        }
                        if (!inUl) {
                            out.push('<ul class="ai-md-list">');
                            inUl = true;
                        }
                        out.push('<li>' + formatInline(ulMatch[1]) + '</li>');
                        continue;
                    }

                    const olMatch = line.match(/^\d+\.\s+(.+)$/);
                    if (olMatch) {
                        if (inUl) {
                            out.push('</ul>');
                            inUl = false;
                        }
                        if (!inOl) {
                            out.push('<ol class="ai-md-list">');
                            inOl = true;
                        }
                        out.push('<li>' + formatInline(olMatch[1]) + '</li>');
                        continue;
                    }

                    const h1Match = line.match(/^#\s+(.+)$/);
                    if (h1Match) {
                        closeLists();
                        out.push('<h3 class="ai-md-h1">' + formatInline(h1Match[1]) + '</h3>');
                        continue;
                    }

                    const h2Match = line.match(/^##\s+(.+)$/);
                    if (h2Match) {
                        closeLists();
                        out.push('<h4 class="ai-md-h2">' + formatInline(h2Match[1]) + '</h4>');
                        continue;
                    }

                    const h3Match = line.match(/^###\s+(.+)$/);
                    if (h3Match) {
                        closeLists();
                        out.push('<h5 class="ai-md-h3">' + formatInline(h3Match[1]) + '</h5>');
                        continue;
                    }

                    closeLists();
                    out.push('<p class="ai-md-p">' + formatInline(line) + '</p>');
                }

                closeLists();

                return out.join('');
            };

            chat.addEventListener('scroll', () => {
                shouldStickToBottom = isNearBottom();

                if (chat.scrollTop <= 18 && hasMoreHistory && !isLoadingHistory) {
                    loadHistory({
                        appendOlder: true
                    });
                }
            });

            const appendMessage = (role, text, meta = '', prepend = false) => {
                const keepBottom = shouldStickToBottom;
                const row = document.createElement('div');
                row.className = 'msg-row ' + role;

                const wrapper = document.createElement('div');
                const bubble = document.createElement('div');
                bubble.className = 'msg-bubble';
                if (role === 'assistant') {
                    bubble.innerHTML = formatAssistantText(text);
                } else {
                    bubble.textContent = text;
                }

                const metaEl = document.createElement('div');
                metaEl.className = 'msg-meta';
                metaEl.textContent = meta || (role === 'user' ? 'Anda' : 'Asisten');

                wrapper.appendChild(bubble);
                wrapper.appendChild(metaEl);
                row.appendChild(wrapper);

                if (prepend && chat.firstChild) {
                    chat.insertBefore(row, chat.firstChild);
                } else {
                    chat.appendChild(row);
                }

                if (!prepend && keepBottom) {
                    scrollToBottom();
                }

                return row;
            };

            const loadHistory = async ({
                appendOlder = false
            } = {}) => {
                if (isLoadingHistory) {
                    return;
                }

                if (appendOlder && (!hasMoreHistory || !nextBeforeId)) {
                    return;
                }

                isLoadingHistory = true;

                const beforeId = appendOlder ? nextBeforeId : null;
                const url = new URL('{{ route('assistant.history') }}', window.location.origin);

                if (beforeId) {
                    url.searchParams.set('before_id', String(beforeId));
                }
                url.searchParams.set('limit', '30');

                const previousHeight = chat.scrollHeight;
                const previousTop = chat.scrollTop;

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();

                    if (!response.ok || !data.ok || !Array.isArray(data.items)) {
                        return;
                    }

                    if (!appendOlder) {
                        chat.innerHTML = '';
                    }

                    nextBeforeId = data.next_before_id || null;
                    hasMoreHistory = !!data.has_more;

                    if (data.items.length === 0) {
                        if (!appendOlder) {
                            appendMessage('assistant',
                                'Halo, saya AI Asisten. Silakan tanya apa pun tentang fitur dan cara penggunaan dashboard Anda.',
                                'Asisten');
                            scrollToBottom();
                        }
                        return;
                    }

                    data.items.forEach((item) => {
                        appendMessage('user', item.question || '', 'Anda', appendOlder);
                        appendMessage('assistant', item.answer || '', 'Asisten (' + (item.source ||
                            'local') + ')', appendOlder);
                    });

                    if (appendOlder) {
                        const newHeight = chat.scrollHeight;
                        chat.scrollTop = newHeight - previousHeight + previousTop;
                    } else {
                        scrollToBottom();
                        shouldStickToBottom = true;
                    }
                } catch (error) {
                    // Keep default opening message when history fetch fails.
                } finally {
                    isLoadingHistory = false;
                }
            };

            const askAssistant = async (question) => {
                appendMessage('user', question, 'Anda');
                const pendingRow = appendMessage('assistant', 'Sedang memproses jawaban...', 'Asisten');
                submitBtn.disabled = true;

                try {
                    const response = await fetch('{{ route('assistant.ask') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            question,
                            current_path: window.location.pathname,
                            page_title: document.title || ''
                        })
                    });

                    const data = await response.json();
                    if (!response.ok || !data.ok) {
                        throw new Error(data.message || 'Gagal mendapatkan jawaban dari asisten.');
                    }

                    pendingRow.querySelector('.msg-bubble').innerHTML = formatAssistantText(data.answer ||
                        'Maaf, belum ada jawaban.');
                    pendingRow.querySelector('.msg-meta').textContent = 'Asisten (' + (data.source || 'local') +
                        ')';
                } catch (error) {
                    pendingRow.querySelector('.msg-bubble').innerHTML = formatAssistantText(
                        'Terjadi kendala saat memproses pertanyaan. Silakan coba lagi.');
                    pendingRow.querySelector('.msg-meta').textContent = 'Asisten';
                } finally {
                    submitBtn.disabled = false;
                    if (shouldStickToBottom) {
                        scrollToBottom();
                    }
                }
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const question = (questionInput.value || '').trim();
                if (!question) {
                    questionInput.focus();
                    return;
                }

                questionInput.value = '';
                shouldStickToBottom = true;
                await askAssistant(question);
            });

            questionInput.addEventListener('keydown', async (event) => {
                if (event.key !== 'Enter') {
                    return;
                }

                if (event.shiftKey) {
                    return;
                }

                event.preventDefault();
                form.requestSubmit();
            });

            hintButtons.forEach((btn) => {
                btn.addEventListener('click', async () => {
                    const hint = (btn.dataset.hint || '').trim();
                    if (!hint) {
                        return;
                    }
                    await askAssistant(hint);
                });
            });

            loadHistory();
        })();
    </script>
@endsection
