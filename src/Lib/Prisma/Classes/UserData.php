<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

use DateTime;

class UserData
{

    public ?UserData $_avg = null;
    public ?UserData $_count = null;
    public ?UserData $_max = null;
    public ?UserData $_min = null;
    public ?UserData $_sum = null;
    public ?string $id;
    public ?string $name;
    public ?string $email;
    public ?string $password;
    public DateTime|string|null $emailVerified;
    public ?string $image;
    public DateTime|string $createdAt;
    public DateTime|string $updatedAt;
    public ?int $roleId;
    public ?UserRoleData $userRole;
    /** @var ChatData[] */
    public ?array $chats;

    public function __construct(
        DateTime|string $createdAt = new DateTime(),
        DateTime|string $updatedAt = new DateTime(),
        ?string $id = null,
        ?string $name = null,
        ?string $email = null,
        ?string $password = null,
        DateTime|string|null $emailVerified = null,
        ?string $image = null,
        ?int $roleId = null,
        ?UserRoleData $userRole = null,
        ?array $chats = [],
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->emailVerified = $emailVerified;
        $this->image = $image;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->roleId = $roleId;
        $this->userRole = $userRole;
        $this->chats = $chats;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'emailVerified' => $this->emailVerified ? $this->emailVerified->format('Y-m-d H:i:s') : null,
            'image' => $this->image,
            'createdAt' => $this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : null,
            'updatedAt' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'roleId' => $this->roleId,
            'userRole' => $this->userRole,
            'chats' => $this->chats
        ];
    }
}