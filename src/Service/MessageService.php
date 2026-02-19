<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use App\Registry\MessageHandlerRegistry;

final class MessageService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageHandlerRegistry $registry
    ) {}

    public function process(Message $message): void
    {
        $this->registry->handle($message);
        $this->em->flush();
    }

    public function create(array $data): Message
    {
        $message = new Message();
        $message->setSubject($data['subject']);
        $message->setMessage($data['message']);
        $message->setDate(new \DateTimeImmutable($data['date']));
        $message->setSenderName($data['senderName']);
        $message->setType($data['type']);

        $this->em->persist($message);
        $this->em->flush();

        return $message;
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Message::class)->findAll();
    }

    public function find(int $id): ?Message
    {
        return $this->em->getRepository(Message::class)->find($id);
    }

    public function update(Message $message, array $data): Message
    {
        $message->setSubject($data['subject']);
        $message->setMessage($data['message']);
        $message->setDate(new \DateTimeImmutable($data['date']));
        $message->setSenderName($data['senderName']);
        $message->setType($data['type']);

        $this->em->flush();

        return $message;
    }

    public function delete(Message $message): void
    {
        $this->em->remove($message);
        $this->em->flush();
    }
}
