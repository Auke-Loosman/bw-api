<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Message;

interface MessageHandlerInterface
{
    public function supports(string $type): bool;

    public function handle(Message $message): void;
}
