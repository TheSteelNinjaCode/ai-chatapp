<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use DateTime;

class ChatData
{

    public ?ChatData $_avg = null;
    public ?ChatData $_count = null;
    public ?ChatData $_max = null;
    public ?ChatData $_min = null;
    public ?ChatData $_sum = null;
    public ?string $id;
    public string $title;
    public DateTime|string $createdAt;
    public DateTime|string $updatedAt;
    public string $userId;
    public ?UserData $user;
    /** @var MessageData[] */
    public ?array $messages;

    public function __construct(
        string $title = 'New Chat',
        DateTime|string $createdAt = new DateTime(),
        DateTime|string $updatedAt = new DateTime(),
        string $userId,
        ?string $id = null,
        ?UserData $user = null,
        ?array $messages = [],
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->userId = $userId;
        $this->user = $user;
        $this->messages = $messages;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'userId' => $this->userId,
            'user' => $this->user,
            'messages' => $this->messages
        ];
    }
}