(function () {
    if (window.location.pathname.startsWith("/assistant")) {
        return;
    }

    const csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content") || "";
    if (!csrfToken) {
        return;
    }

    const container = document.createElement("div");
    container.innerHTML = `
        <div class="ai-widget" id="ai-widget">
            <div class="ai-widget-head">
                <h4 class="ai-widget-title"><i class="fas fa-robot mr-1"></i> AI Asisten</h4>
                <button type="button" class="ai-widget-close" id="ai-widget-close" aria-label="Tutup">×</button>
            </div>
            <div class="ai-widget-body">
                <div class="ai-hints">
                    <button type="button" class="ai-hint" data-ai-hint="Fitur apa saja di dashboard saya?">Fitur saya</button>
                    <button type="button" class="ai-hint" data-ai-hint="Cara cek invoice dan status pembayaran">Cek invoice</button>
                    <button type="button" class="ai-hint" data-ai-hint="Cara pakai hitung halaman naskah">Hitung halaman</button>
                    <a href="/assistant/export" class="ai-hint" title="Export riwayat chat ke CSV">Export CSV</a>
                </div>
                <div class="ai-chat-box" id="ai-chat-box"></div>
                <form class="ai-form" id="ai-form">
                    <div class="form-group mb-2">
                        <textarea id="ai-question" class="form-control form-control-sm" rows="3" maxlength="3000" placeholder="Tulis pertanyaan Anda..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block" id="ai-submit">Kirim</button>
                </form>
            </div>
        </div>
        <div class="ai-fab">
            <button type="button" id="ai-fab-button" class="ai-fab-button" aria-label="Buka AI Asisten">
                <i class="fas fa-robot"></i>
            </button>
        </div>
    `;

    document.body.appendChild(container);

    const widget = document.getElementById("ai-widget");
    const fabButton = document.getElementById("ai-fab-button");
    const closeButton = document.getElementById("ai-widget-close");
    const chatBox = document.getElementById("ai-chat-box");
    const form = document.getElementById("ai-form");
    const questionInput = document.getElementById("ai-question");
    const submitButton = document.getElementById("ai-submit");
    const hintButtons = Array.from(document.querySelectorAll("[data-ai-hint]"));
    let nextBeforeId = null;
    let hasMoreHistory = true;
    let isLoadingHistory = false;
    let shouldStickToBottom = true;

    const isNearBottom = () => {
        const threshold = 48;
        return (
            chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight <=
            threshold
        );
    };

    const scrollToBottom = () => {
        chatBox.scrollTop = chatBox.scrollHeight;
    };

    const escapeHtml = (value) =>
        String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#39;");

    const formatInline = (value) =>
        value
            .replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>")
            .replace(/`([^`]+)`/g, "<code>$1</code>")
            .replace(
                /(https?:\/\/[^\s<]+)/g,
                '<a class="ai-md-link" href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
            );

    const formatAssistantText = (value) => {
        const lines = escapeHtml(value).replace(/\r\n/g, "\n").split("\n");
        const out = [];
        let inUl = false;
        let inOl = false;

        const closeLists = () => {
            if (inUl) {
                out.push("</ul>");
                inUl = false;
            }
            if (inOl) {
                out.push("</ol>");
                inOl = false;
            }
        };

        for (const rawLine of lines) {
            const line = rawLine.trim();

            if (line === "") {
                closeLists();
                out.push("<br>");
                continue;
            }

            const ulMatch = line.match(/^[-*]\s+(.+)$/);
            if (ulMatch) {
                if (inOl) {
                    out.push("</ol>");
                    inOl = false;
                }
                if (!inUl) {
                    out.push('<ul class="ai-md-list">');
                    inUl = true;
                }
                out.push("<li>" + formatInline(ulMatch[1]) + "</li>");
                continue;
            }

            const olMatch = line.match(/^\d+\.\s+(.+)$/);
            if (olMatch) {
                if (inUl) {
                    out.push("</ul>");
                    inUl = false;
                }
                if (!inOl) {
                    out.push('<ol class="ai-md-list">');
                    inOl = true;
                }
                out.push("<li>" + formatInline(olMatch[1]) + "</li>");
                continue;
            }

            const h1Match = line.match(/^#\s+(.+)$/);
            if (h1Match) {
                closeLists();
                out.push(
                    '<h3 class="ai-md-h1">' +
                        formatInline(h1Match[1]) +
                        "</h3>",
                );
                continue;
            }

            const h2Match = line.match(/^##\s+(.+)$/);
            if (h2Match) {
                closeLists();
                out.push(
                    '<h4 class="ai-md-h2">' +
                        formatInline(h2Match[1]) +
                        "</h4>",
                );
                continue;
            }

            const h3Match = line.match(/^###\s+(.+)$/);
            if (h3Match) {
                closeLists();
                out.push(
                    '<h5 class="ai-md-h3">' +
                        formatInline(h3Match[1]) +
                        "</h5>",
                );
                continue;
            }

            closeLists();
            out.push('<p class="ai-md-p">' + formatInline(line) + "</p>");
        }

        closeLists();

        return out.join("");
    };

    chatBox.addEventListener("scroll", () => {
        shouldStickToBottom = isNearBottom();

        if (chatBox.scrollTop <= 18 && hasMoreHistory && !isLoadingHistory) {
            loadHistory({ appendOlder: true });
        }
    });

    const appendMessage = (role, text, meta, prepend = false) => {
        const keepBottom = shouldStickToBottom;
        const row = document.createElement("div");
        row.className = "ai-msg " + role;

        const wrap = document.createElement("div");
        const bubble = document.createElement("div");
        bubble.className = "ai-bubble";
        if (role === "assistant") {
            bubble.innerHTML = formatAssistantText(text);
        } else {
            bubble.textContent = text;
        }

        const metaDiv = document.createElement("div");
        metaDiv.className = "ai-meta";
        metaDiv.textContent = meta || (role === "user" ? "Anda" : "Asisten");

        wrap.appendChild(bubble);
        wrap.appendChild(metaDiv);
        row.appendChild(wrap);

        if (prepend && chatBox.firstChild) {
            chatBox.insertBefore(row, chatBox.firstChild);
        } else {
            chatBox.appendChild(row);
        }

        if (!prepend && keepBottom) {
            scrollToBottom();
        }

        return row;
    };

    const loadHistory = async ({ appendOlder = false } = {}) => {
        if (isLoadingHistory) {
            return;
        }

        if (appendOlder && (!hasMoreHistory || !nextBeforeId)) {
            return;
        }

        isLoadingHistory = true;

        const beforeId = appendOlder ? nextBeforeId : null;
        const url = new URL("/assistant/history", window.location.origin);

        if (beforeId) {
            url.searchParams.set("before_id", String(beforeId));
        }
        url.searchParams.set("limit", "30");

        const previousHeight = chatBox.scrollHeight;
        const previousTop = chatBox.scrollTop;

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    Accept: "application/json",
                },
                credentials: "same-origin",
            });

            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error("Failed");
            }

            if (!appendOlder) {
                chatBox.innerHTML = "";
            }

            nextBeforeId = data.next_before_id || null;
            hasMoreHistory = !!data.has_more;

            if (!Array.isArray(data.items) || data.items.length === 0) {
                if (!appendOlder) {
                    appendMessage(
                        "assistant",
                        "Halo, saya AI Asisten. Tanyakan apa pun terkait fitur dashboard Anda.",
                        "Asisten",
                    );
                    scrollToBottom();
                }
                return;
            }

            data.items.forEach((item) => {
                appendMessage("user", item.question || "", "Anda", appendOlder);
                appendMessage(
                    "assistant",
                    item.answer || "",
                    "Asisten (" + (item.source || "local") + ")",
                    appendOlder,
                );
            });

            if (appendOlder) {
                const newHeight = chatBox.scrollHeight;
                chatBox.scrollTop = newHeight - previousHeight + previousTop;
            } else {
                scrollToBottom();
                shouldStickToBottom = true;
            }
        } catch (e) {
            if (!appendOlder) {
                chatBox.innerHTML = "";
                appendMessage(
                    "assistant",
                    "Halo, saya AI Asisten. Tanyakan apa pun terkait fitur dashboard Anda.",
                    "Asisten",
                );
                scrollToBottom();
                shouldStickToBottom = true;
            }
        } finally {
            isLoadingHistory = false;
        }
    };

    const ask = async (question) => {
        appendMessage("user", question, "Anda");
        const pending = appendMessage(
            "assistant",
            "Sedang memproses jawaban...",
            "Asisten",
        );
        submitButton.disabled = true;

        try {
            const response = await fetch("/assistant/ask", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                credentials: "same-origin",
                body: JSON.stringify({
                    question,
                    current_path: window.location.pathname,
                    page_title: document.title || "",
                }),
            });

            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data.message || "Gagal");
            }

            pending.querySelector(".ai-bubble").innerHTML = formatAssistantText(
                data.answer || "Belum ada jawaban.",
            );
            pending.querySelector(".ai-meta").textContent =
                "Asisten (" + (data.source || "local") + ")";
        } catch (err) {
            pending.querySelector(".ai-bubble").innerHTML = formatAssistantText(
                "Terjadi kendala saat memproses pertanyaan. Silakan coba lagi.",
            );
            pending.querySelector(".ai-meta").textContent = "Asisten";
        } finally {
            submitButton.disabled = false;
            if (shouldStickToBottom) {
                scrollToBottom();
            }
        }
    };

    fabButton.addEventListener("click", async () => {
        widget.classList.toggle("open");
        if (widget.classList.contains("open")) {
            await loadHistory();
            questionInput.focus();
        }
    });

    closeButton.addEventListener("click", () => {
        widget.classList.remove("open");
    });

    form.addEventListener("submit", async (event) => {
        event.preventDefault();
        const question = (questionInput.value || "").trim();
        if (!question) {
            questionInput.focus();
            return;
        }

        questionInput.value = "";
        shouldStickToBottom = true;
        await ask(question);
    });

    questionInput.addEventListener("keydown", (event) => {
        if (event.key !== "Enter") {
            return;
        }

        if (event.shiftKey) {
            return;
        }

        event.preventDefault();
        form.requestSubmit();
    });

    hintButtons.forEach((button) => {
        button.addEventListener("click", async () => {
            const question = (button.dataset.aiHint || "").trim();
            if (!question) {
                return;
            }

            if (!widget.classList.contains("open")) {
                widget.classList.add("open");
                await loadHistory();
            }

            await ask(question);
        });
    });
})();
