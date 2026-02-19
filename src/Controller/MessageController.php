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

    #[Route('/api/messages', name: 'app_message_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $messages = $em->getRepository(Message::class)->findAll();

        $data = array_map(
            fn (Message $message) => [
                'id' => $message->getId(),
                'subject' => $message->getSubject(),
                'type' => $message->getType(),
            ],
            $messages
        );

        return new JsonResponse($data);
    }

    #[Route('/api/messages/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $message = $em->getRepository(Message::class)->find($id);

        if (!$message) {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse([
            'id' => $message->getId(),
            'subject' => $message->getSubject(),
            'message' => $message->getMessage(),
            'date' => $message->getDate()->format('Y-m-d H:i:s'),
            'senderName' => $message->getSenderName(),
            'type' => $message->getType(),
        ]);
    }

    #[Route('/api/messages/{id}', name: 'app_message_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $message = $em->getRepository(Message::class)->find($id);

        if (!$message) {
            return new JsonResponse(null, 404);
        }

        $data = json_decode($request->getContent(), true);

        $message->setSubject($data['subject']);
        $message->setMessage($data['message']);
        $message->setDate(new \DateTimeImmutable($data['date']));
        $message->setSenderName($data['senderName']);
        $message->setType($data['type']);

        $em->flush();

        return new JsonResponse([
            'id' => $message->getId(),
            'subject' => $message->getSubject(),
            'type' => $message->getType(),
        ]);
    }
}
