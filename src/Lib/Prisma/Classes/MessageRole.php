<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

enum MessageRole: string
{
    case SYSTEM = 'SYSTEM';
    case USER = 'USER';
    case ASSISTANT = 'ASSISTANT';
}