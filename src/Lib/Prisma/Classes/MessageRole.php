<?php

declare(strict_types=1);

namespace Lib\Prisma\Classes;

enum MessageRole: string
{
    case system = 'system';
    case user = 'user';
    case assistant = 'assistant';
}