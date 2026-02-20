<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use App\Registry\MessageHandlerRegistry;
use Doctrine\ORM\EntityRepository;
use App\DTO\MessageInput;

final class MessageService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageHandlerRegistry $registry
    ) {}

    private function repository(): EntityRepository
    {
        return $this->em->getRepository(Message::class);
    }

    public function process(Message $message): void
    {
        $this->registry->handle($message);
        $this->em->flush();
    }

    public function createFromDto(MessageInput $input): Message
    {
        $message = new Message();

        $message->setSubject($input->subject);
        $message->setMessage($input->message);
        $message->setDate(new \DateTimeImmutable($input->date));
        $message->setSenderName($input->senderName);
        $message->setType($input->type);

        $this->em->persist($message);
        $this->em->flush();

        return $message;
    }

    public function findAll(): array
    {
        return $this->repository()->findAll();
    }

    public function find(int $id): ?Message
    {
        return $this->repository()->find($id);
    }

    public function findByType(string $type): array
    {
        return $this->repository()->findBy(['type' => $type]);
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
