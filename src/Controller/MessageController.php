<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

final class MessageController
{
    #[Route('/api/messages', name: 'app_message_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $message = new Message();
        $message->setSubject($data['subject']);
        $message->setMessage($data['message']);
        $message->setDate(new \DateTimeImmutable($data['date']));
        $message->setSenderName($data['senderName']);
        $message->setType($data['type']);

        $em->persist($message);
        $em->flush();

        return new JsonResponse([
            'id' => $message->getId(),
            'subject' => $message->getSubject(),
            'type' => $message->getType(),
        ], 201);
    }
}
