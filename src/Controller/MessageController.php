<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Message;
use App\Service\MessageService;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\MessageInput;

final class MessageController
{
    public function __construct(
        private readonly MessageService $service,
        private readonly ValidatorInterface $validator
    ) {}

    #[Route('/api/messages', name: 'app_message_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $input = new MessageInput();
        $input->subject = $data['subject'] ?? '';
        $input->message = $data['message'] ?? '';
        $input->date = $data['date'] ?? '';
        $input->senderName = $data['senderName'] ?? '';
        $input->type = $data['type'] ?? '';

        $errors = $this->validator->validate($input);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        $message = $this->service->createFromDto($input);

        return new JsonResponse([
            'id' => $message->getId(),
            'subject' => $message->getSubject(),
            'type' => $message->getType(),
        ], 201);
    }

    #[Route('/api/messages', name: 'app_message_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $messages = $this->service->findAll();

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
    public function show(int $id): JsonResponse
    {
        $message = $this->service->find($id);

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
    public function update(int $id, Request $request): JsonResponse
    {
        $message = $this->service->find($id);

        if (!$message) {
            return new JsonResponse(null, 404);
        }

        $data = json_decode($request->getContent(), true);
        $updated = $this->service->update($message, $data);

        return new JsonResponse([
            'id' => $updated->getId(),
            'subject' => $updated->getSubject(),
            'type' => $updated->getType(),
        ]);
    }

    #[Route('/api/messages/{id}', name: 'app_message_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $message = $this->service->find($id);

        if (!$message) {
            return new JsonResponse(null, 404);
        }

        $this->service->delete($message);

        return new JsonResponse(null, 204);
    }
}
