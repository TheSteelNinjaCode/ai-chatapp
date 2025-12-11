<?php

use Lib\Prisma\Classes\Prisma;

$prisma = Prisma::getInstance();

$chats = $prisma->chat->findMany();

print_r($chats);
