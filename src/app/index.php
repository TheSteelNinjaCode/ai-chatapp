<?php

use app\_components\ToggleTheme;
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
            <div class="px-2 py-1.5 text-xs font-semibold text-muted-foreground uppercase">Recent Chats</div>

            <button class="w-full flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium bg-accent text-accent-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
                Project Planning
            </button>
            <button class="w-full flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-muted/50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2-2z" />
                </svg>
                Code Review: PulsePoint
            </button>
            <button class="w-full flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-muted/50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2-2z" />
                </svg>
                Marketing Ideas
            </button>
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
            <div class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                <span class="text-foreground">PulsePoint AI 4.0</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground/50">
                    <path d="m6 9 6 6 6-6" />
                </svg>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <ToggleTheme />
            <button class="p-2 text-muted-foreground hover:text-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.09a2 2 0 0 1-1-1.74v-.47a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </button>
        </div>
    </header>

    <main class="col-start-2 row-start-2 overflow-y-auto p-4 md:p-10 space-y-8 scroll-smooth">

        <div class="flex items-start gap-4 max-w-3xl mx-auto">
            <div class="h-8 w-8 rounded-full bg-primary shrink-0 flex items-center justify-center text-primary-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8" />
                    <rect width="16" height="12" x="4" y="8" rx="2" />
                    <path d="M2 14h2" />
                    <path d="M20 14h2" />
                    <path d="M15 13v2" />
                    <path d="M9 13v2" />
                </svg>
            </div>
            <div class="space-y-2">
                <div class="font-semibold text-sm">PulsePoint AI</div>
                <div class="text-sm text-foreground/90 leading-relaxed">
                    Hello Jefferson! How can I help you with your framework today? I can assist with Tailwind configurations, PHP components, or reactive logic.
                </div>
            </div>
        </div>

        <div class="flex items-start gap-4 max-w-3xl mx-auto flex-row-reverse">
            <div class="h-8 w-8 rounded-full bg-muted shrink-0 flex items-center justify-center text-muted-foreground font-bold text-xs">JA</div>
            <div class="space-y-2">
                <div class="bg-secondary text-secondary-foreground px-4 py-3 rounded-2xl rounded-tr-sm text-sm">
                    I need to create a UI for a chat app. It should use Shadcn colors and have a fixed bottom input.
                </div>
            </div>
        </div>

        <div class="flex items-start gap-4 max-w-3xl mx-auto">
            <div class="h-8 w-8 rounded-full bg-primary shrink-0 flex items-center justify-center text-primary-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8" />
                    <rect width="16" height="12" x="4" y="8" rx="2" />
                    <path d="M2 14h2" />
                    <path d="M20 14h2" />
                    <path d="M15 13v2" />
                    <path d="M9 13v2" />
                </svg>
            </div>
            <div class="space-y-2">
                <div class="font-semibold text-sm">PulsePoint AI</div>
                <div class="text-sm text-foreground/90 leading-relaxed">
                    <p>I can certainly generate that for you. Using a CSS Grid with <code>grid-rows-[auto_1fr_auto]</code> is the best way to handle the layout.</p>
                    <br />
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>Sidebar:</strong> Fixed width, spans full height.</li>
                        <li><strong>Content:</strong> Uses <code>overflow-y-auto</code> to scroll independently.</li>
                        <li><strong>Input:</strong> Remains fixed at the bottom.</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="h-4"></div>
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
                    class="w-full bg-transparent border-0 focus:ring-0 resize-none py-3 max-h-32 text-sm placeholder:text-muted-foreground scrollbar-hide"
                    style="min-height: 44px;"></textarea>

                <button class="p-2 mb-1 rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors disabled:opacity-50">
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