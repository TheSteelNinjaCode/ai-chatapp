<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use DateTime;

class MessageData
{

    public ?MessageData $_avg = null;
    public ?MessageData $_count = null;
    public ?MessageData $_max = null;
    public ?MessageData $_min = null;
    public ?MessageData $_sum = null;
    public ?string $id;
    public string $content;
    public MessageRole $role;
    public DateTime|string $createdAt;
    public ?int $promptTokens;
    public ?int $completionTokens;
    public string $chatId;
    public ?ChatData $chat;

    public function __construct(
        string $content,
        MessageRole $role,
        DateTime|string $createdAt = new DateTime(),
        string $chatId,
        ?string $id = null,
        ?int $promptTokens = null,
        ?int $completionTokens = null,
        ?ChatData $chat = null,
    ) {
        $this->id = $id;
        $this->content = $content;
        $this->role = $role;
        $this->createdAt = $createdAt;
        $this->promptTokens = $promptTokens;
        $this->completionTokens = $completionTokens;
        $this->chatId = $chatId;
        $this->chat = $chat;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'role' => $this->role,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'promptTokens' => $this->promptTokens,
            'completionTokens' => $this->completionTokens,
            'chatId' => $this->chatId,
            'chat' => $this->chat
        ];
    }
}