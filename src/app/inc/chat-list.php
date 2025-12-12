<?php

use Lib\PPIcons\MessageSquare;

use Lib\Prisma\Classes\Prisma;

$prisma = Prisma::getInstance();

$chats = $prisma->chat->findMany([
    'orderBy' => [
        'updatedAt' => 'desc',
    ],
    'take' => 10,
]);

?>

<template pp-for="chat in chats">
    <button class="w-full flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium {selectedChat === chat.id ? 'bg-accent text-accent-foreground' : 'text-muted-foreground hover:bg-muted/50'} transition-colors">
        <MessageSquare class="size-4" />
        {chat.title}
    </button>
</template>

<script>
    const [chats, setChats] = pp.state(<?= json_encode($chats) ?>);
    const [selectedChat, setSelectedChat] = pp.state(<?= json_encode($chats[0]->id) ?>);
</script>