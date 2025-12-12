<div class="max-w-xl space-y-3">
    <div class="space-y-1">
        <label class="text-sm font-medium">Ask something</label>

        <input
            class="w-full rounded-md border bg-background px-3 py-2 text-sm outline-none focus:ring-2"
            placeholder="Type your message and press Enter…"
            value="{prompt}"
            oninput="setPrompt(event.target.value)"
            onkeydown="(event.key === 'Enter' && !event.shiftKey) ? (event.preventDefault(), sendMessage()) : null"
            disabled="{disabled}" />
    </div>

    <div class="flex items-center gap-2">
        <button
            class="rounded-md border px-3 py-2 text-sm"
            onclick="sendMessage()"
            disabled="{disabled}">
            {disabled ? "Streaming..." : "Send"}
        </button>

        <button
            class="rounded-md border px-3 py-2 text-sm"
            onclick="clearChat()"
            disabled="{disabled}">
            Clear
        </button>
    </div>

    <!-- Transcript -->
    <div id="chatBox" class="rounded-md border p-3 text-sm h-96 overflow-auto space-y-3">
        <div pp-if="{chat.length === 0}" class="text-muted-foreground">
            Assistant response will appear here…
        </div>

        <template pp-for="m in chat">
            <div class="{m.role === 'user' ? 'flex justify-end' : 'flex justify-start'}">
                <div class="max-w-[85%] rounded-lg px-3 py-2 whitespace-pre-wrap border {m.role === 'user'
            ? 'bg-primary text-primary-foreground border-primary/30'
            : 'bg-muted/40 text-foreground border-border'}">{m.content}</div>
            </div>
        </template>
    </div>
</div>

<script>
    // UI state
    const [prompt, setPrompt] = pp.state("");
    const [disabled, setDisabled] = pp.state(false);

    // Rendered transcript
    const [chat, setChat] = pp.state([]); // [{ role, content }]

    // System prompt sent to API only
    const SYSTEM = {
        role: "system",
        content: "You are a helpful assistant."
    };

    function clearChat() {
        setChat([]);
        setPrompt("");
    }

    // Keep transcript pinned to bottom while chat changes
    pp.effect(() => {
        const el = document.getElementById("chatBox");
        if (!el) return;
        requestAnimationFrame(() => {
            el.scrollTop = el.scrollHeight;
        });
    }, [chat]);

    async function sendMessage() {
        const text = (prompt || "").trim();
        if (!text || disabled) return;

        // Snapshot history BEFORE adding the new UI bubbles
        const history = Array.isArray(chat) ? [...chat] : [];

        // Add user bubble + placeholder assistant bubble for streaming
        setChat(prev => [
            ...(Array.isArray(prev) ? prev : []),
            {
                role: "user",
                content: text
            },
            {
                role: "assistant",
                content: ""
            }
        ]);

        setDisabled(true);
        setPrompt("");

        // Build API context
        const messages = [SYSTEM, ...history, {
            role: "user",
            content: text
        }];

        let started = false;

        try {
            await streamChat(messages, {
                onToken(t) {
                    // Remove leading whitespace on the first token (prevents initial empty gap)
                    if (!started) {
                        t = String(t).replace(/^\s+/, "");
                        started = true;
                    }

                    setChat(prev => {
                        const arr = Array.isArray(prev) ? [...prev] : [];
                        const last = arr[arr.length - 1];

                        // Ensure last is assistant placeholder
                        if (!last || last.role !== "assistant") {
                            arr.push({
                                role: "assistant",
                                content: t
                            });
                            return arr;
                        }

                        arr[arr.length - 1] = {
                            ...last,
                            content: (last.content || "") + t
                        };
                        return arr;
                    });
                },
                onDone() {
                    // No-op: transcript is already updated
                },
                onError(err) {
                    const msg = err?.message || "Streaming error";
                    setChat(prev => [
                        ...(Array.isArray(prev) ? prev : []),
                        {
                            role: "assistant",
                            content: `Error: ${msg}`
                        }
                    ]);
                }
            });
        } finally {
            setDisabled(false);
        }
    }

    async function streamChat(messages, {
        onToken,
        onDone,
        onError
    } = {}) {
        const res = await fetch("/api/chat-stream/", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "text/event-stream"
            },
            body: JSON.stringify({
                messages
            })
        });

        if (!res.ok || !res.body) throw new Error(`HTTP ${res.status}`);

        const reader = res.body.getReader();
        const decoder = new TextDecoder();
        let buffer = "";

        while (true) {
            const {
                value,
                done
            } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, {
                stream: true
            });

            const parts = buffer.split("\n\n");
            buffer = parts.pop() || "";

            for (const frame of parts) {
                const lines = frame.split("\n");
                let event = "message";
                let data = "";

                for (const line of lines) {
                    if (line.startsWith("event:")) event = line.slice(6).trim();
                    if (line.startsWith("data:")) data += line.slice(5).trim();
                }

                if (event === "token") {
                    const payload = JSON.parse(data);
                    onToken?.(payload.text);
                } else if (event === "done") {
                    onDone?.();
                    return;
                } else if (event === "error") {
                    const payload = JSON.parse(data);
                    onError?.(payload);
                    return;
                }
            }
        }

        onDone?.();
    }
</script>