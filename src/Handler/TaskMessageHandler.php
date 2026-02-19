<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Message;

final class TaskMessageHandler implements MessageHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'task';
    }

    public function handle(Message $message): void
    {
        $message->setProcessedAt(new \DateTimeImmutable());
    }
}
