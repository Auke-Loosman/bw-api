<?php

declare(strict_types=1);

namespace App\Registry;

use App\Entity\Message;
use App\Handler\MessageHandlerInterface;

final class MessageHandlerRegistry
{
    /**
     * @param iterable<MessageHandlerInterface> $handlers
     */
    public function __construct(
        private readonly iterable $handlers
    ) {}

    public function handle(Message $message): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($message->getType())) {
                $handler->handle($message);
                return;
            }
        }

        throw new \RuntimeException('No handler found for type: ' . $message->getType());
    }
}
