<?php

use Lib\PHPXUI\Button;
use Lib\PPIcons\{Bot, Plus};

use app\_components\{Config, SelectAIModel, ToggleTheme};
use PP\IncludeTracker;

use Lib\Prisma\Classes\Prisma;

$prisma = Prisma::getInstance();

$chats = $prisma->chat->findMany([
    'orderBy' => [
        'updatedAt' => 'desc',
    ],
    'take' => 10,
]);

?>

<div class="grid h-screen w-full grid-cols-[280px_1fr] grid-rows-[60px_1fr_auto]">

    <aside class="row-span-3 border-r border-border bg-card hidden md:flex flex-col">
        <div class="h-[60px] flex items-center px-6 border-b border-border">
            <span class="font-bold text-lg tracking-tight flex items-center gap-2">
                <div class="h-6 w-6 rounded-full bg-primary"></div>
                PulseAI
            </span>
        </div>

        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
            <Button class="w-full justify-start" variant="ghost" onclick="clearChat()">
                <Plus class="size-4" />
                New Chat
            </Button>
            <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground uppercase">Recent Chats</div>
            <?php IncludeTracker::render(APP_PATH . '/inc/chat-list.php',[
                'chats' => '{chats}'
            ]) ?>
        </nav>

        <div class="p-4 border-t border-border">
            <button class="flex items-center gap-3 w-full px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                <div class="h-8 w-8 rounded-full bg-secondary flex items-center justify-center text-xs text-foreground font-bold">JA</div>
                <div class="flex flex-col items-start">
                    <span class="text-sm">Jefferson A.</span>
                    <span class="text-xs text-muted-foreground">Pro Plan</span>
                </div>
            </button>
        </div>
    </aside>

    <header class="col-start-2 row-start-1 flex items-center justify-between border-b border-border bg-background px-6">
        <div class="flex items-center gap-2">
            <button class="md:hidden p-2 -ml-2 text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <SelectAIModel />
        </div>
        <div class="flex items-center gap-2">
            <ToggleTheme />
            <Config />
        </div>
    </header>

    <main id="chatScroll" class="col-start-2 row-start-2 overflow-y-auto p-4 md:p-10 space-y-8 scroll-smooth">

        <div class="max-w-3xl mx-auto space-y-8">
            <p hidden="{chat.length > 0}">¿En qué estás pensando hoy?</p>

            <template pp-for="m in chat">
                <!-- Wrapper aligns left/right -->
                <div class="flex items-start gap-4 {m.role === 'user' ? 'flex-row-reverse' : ''}">

                    <!-- Avatar -->
                    <div class="h-8 w-8 rounded-full shrink-0 flex items-center justify-center
                    {m.role === 'user'
                        ? 'bg-muted text-muted-foreground font-bold text-xs'
                        : 'bg-primary text-primary-foreground'}">
                        {m.role === 'user' ? 'JA' : ''}
                        <Bot class="size-4" hidden="{m.role === 'user'}" />
                    </div>

                    <!-- Message -->
                    <div class="space-y-2 w-full">
                        <div hidden="{m.role === 'user'}" class="font-semibold text-sm">PulsePoint AI</div>

                        <div class="px-4 py-3 rounded-2xl text-sm whitespace-pre-wrap leading-relaxed
                        {m.role === 'user'
                            ? 'bg-secondary text-secondary-foreground rounded-tr-sm'
                            : 'text-foreground/90'}">{m.content}</div>
                    </div>

                </div>
            </template>

            <div class="h-4"></div>
        </div>

    </main>

    <footer class="col-start-2 row-start-3 p-4 md:p-6 bg-background">
        <div class="max-w-3xl mx-auto">
            <div class="relative flex items-end gap-2 p-3 border border-input rounded-xl bg-card shadow-sm focus-within:ring-1 focus-within:ring-ring focus-within:border-ring transition-all">

                <button class="p-2 text-muted-foreground hover:text-foreground transition-colors pb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48" />
                    </svg>
                </button>

                <textarea
                    rows="1"
                    placeholder="Message PulsePoint..."
                    class="w-full bg-transparent outline-0 border-0 focus:ring-0 resize-none py-3 max-h-32 text-sm placeholder:text-muted-foreground scrollbar-hide"
                    style="min-height: 44px;"
                    value="{prompt}"
                    oninput="handleInput(event)"
                    onkeydown="handleKeydown(event)"
                    disabled="{disabled}"></textarea>

                <button
                    class="p-2 mb-1 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors disabled:opacity-50"
                    onclick="sendMessage()"
                    disabled="{disabled}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="m12 5 7 7-7 7" />
                    </svg>
                </button>
            </div>
            <div class="text-center text-xs text-muted-foreground mt-2">
                PulsePoint AI can make mistakes. Check important info.
            </div>
        </div>
    </footer>

</div>

<script>
    const [prompt, setPrompt] = pp.state("");
    const [disabled, setDisabled] = pp.state(false);
    const [chats, setChats] = pp.state(<?= json_encode($chats) ?>);

    // Seed with your initial assistant greeting
    const [chat, setChat] = pp.state([]);

    const SYSTEM = {
        role: "system",
        content: "You are PulsePoint AI, a helpful assistant for Prisma PHP, PulsePoint, and Tailwind/shadcn UI."
    };

    function handleInput(event) {
        setPrompt(event.target.value);

        // Optional: autosize textarea
        const el = event.target;
        el.style.height = "auto";
        el.style.height = Math.min(el.scrollHeight, 160) + "px";
    }

    function handleKeydown(event) {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    }

    // Keep chat pinned to bottom on updates
    pp.effect(() => {
        const el = document.getElementById("chatScroll");
        if (!el) return;
        requestAnimationFrame(() => {
            el.scrollTop = el.scrollHeight;
        });
    }, [chat]);

    function clearChat() {
        setChat([]);
        setPrompt("");
    }

    async function sendMessage() {
        const text = (prompt || "").trim();
        if (!text || disabled) return;

        // Snapshot history BEFORE UI mutations
        const history = Array.isArray(chat) ? [...chat] : [];

        // Add user + placeholder assistant
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

        const messages = [SYSTEM, ...history, {
            role: "user",
            content: text
        }];

        let started = false;

        try {
            await streamChat(messages, {
                onToken(t) {
                    if (!started) {
                        t = String(t).replace(/^\s+/, "");
                        started = true;
                    }

                    setChat(prev => {
                        const arr = Array.isArray(prev) ? [...prev] : [];
                        const last = arr[arr.length - 1];

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