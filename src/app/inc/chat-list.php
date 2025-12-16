<?php

use Lib\PPIcons\{Circle, Ellipsis, LoaderCircle, MessageSquare, Trash};
use Lib\Prisma\Classes\Prisma;
use PP\Validator;

function getChat($data)
{
    $id = Validator::cuid($data->id ?? null);

    if (!$id) {
        return [
            'error' => true,
            'message' => 'Invalid chat ID.'
        ];
    }

    $prisma = Prisma::getInstance();
    $chat = $prisma->chat->findUnique([
        'where' => [
            'id' => $id
        ],
        'include' => [
            'messages' => true
        ]
    ]);

    if (!$chat) {
        return [
            'error' => true,
            'message' => 'Chat not found.'
        ];
    }

    return [
        'success' => true,
        'chat' => $chat
    ];
}

function deleteChat($data)
{
    $id = Validator::cuid($data->id ?? null);

    if (!$id) {
        return [
            'error' => true,
            'message' => 'Invalid chat ID.'
        ];
    }

    $prisma = Prisma::getInstance();
    $deletedChat = $prisma->chat->delete([
        'where' => [
            'id' => $id
        ]
    ]);

    if (!$deletedChat) {
        return [
            'error' => true,
            'message' => 'Failed to delete chat.'
        ];
    }

    return [
        'success' => true,
        'deletedChatId' => $deletedChat->id
    ];
}

?>

<div class="flex flex-col gap-1">
    <template pp-for="chat in chats">
        <div class="group flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium {currentChatId === chat.id ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-muted/50'} transition-colors" key="{chat.id}">

            <button class="flex-1 min-w-0 flex items-center gap-3 text-left" onclick="handleSelectedChat(chat.id)">
                <MessageSquare class="size-4 shrink-0" />
                <span class="truncate">
                    {chat.title}
                </span>
            </button>

            <button class="shrink-0" onclick="handleDeleteChat(chat.id)" disabled="{loadingIds[chat.id]}">
                <Trash class="size-4 opacity-50 hover:opacity-100 text-red-500 transition-opacity" hidden="{loadingIds[chat.id]}" />
                <LoaderCircle class="size-4 animate-spin" hidden="{!loadingIds[chat.id]}" />
            </button>
        </div>
    </template>
</div>

<script>
    const [loadingIds, setLoadingIds] = pp.state({});
    const chatSearchParam = searchParams.get('chat');

    if (chatSearchParam) {
        handleSelectedChat(chatSearchParam);
    }

    pp.effect(() => {
        console.log("currentChatId chat changed:", currentChatId);
    }, [currentChatId]);

    async function handleSelectedChat(chatId) {
        console.log("chatId: ", chatId);

        const {
            response: {
                success,
                chat,
                error
            }
        } = await pp.fetchFunction('getChat', {
            id: chatId
        });

        if (success) {
            setCurrentChatId(chat.id);
            setChatTitle(chat.title);
            setChat(chat.messages);
            searchParams.set('chat', chat.id);
        } else {
            console.error("Error fetching chat:", error);
        }
    }

    async function handleDeleteChat(chatId) {
        console.log("Delete chatId: ", chatId);

        setLoadingIds(prev => ({
            ...prev,
            [chatId]: true
        }));
        try {
            const {
                response: {
                    success,
                    deletedChatId,
                    error
                }
            } = await pp.fetchFunction('deleteChat', {
                id: chatId
            });

            if (success) {
                setChats(prev => prev.filter(c => c.id !== deletedChatId));

                // If the deleted chat was the current chat, reset currentChatId and chat
                if (currentChatId === deletedChatId) {
                    setCurrentChatId(null);
                    setChat([]);
                    setChatTitle('New Chat');
                    searchParams.delete('chat');
                }
            } else {
                console.error("Error deleting chat:", error);
            }
        } finally {
            setLoadingIds(prev => ({
                ...prev,
                [chatId]: false
            }));
        }
    }
</script>