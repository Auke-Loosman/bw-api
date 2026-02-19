<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\Message;

final class OutgoingMessageHandler implements MessageHandlerInterface
{
    public function supports(string $type): bool
    {
        return $type === 'outgoing';
    }

    public function handle(Message $message): void
    {
        $message->setProcessedAt(new \DateTimeImmutable());
    }
}
