<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MessageApiTest extends WebTestCase
{
    public function testCreateMessage(): void
    {
        $client = static::createClient();

        $payload = [
            'subject' => 'Test Subject',
            'message' => 'Test Message',
            'date' => '2025-01-01 10:00:00',
            'senderName' => 'John',
            'type' => 'incoming'
        ];

        $client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertSame('Test Subject', $data['subject']);
        $this->assertSame('incoming', $data['type']);
    }
}
