<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MessageApiTest extends WebTestCase
{
    public function testCreateMessage(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'Test Subject',
                'message' => 'Test Message',
                'date' => '2025-01-01 10:00:00',
                'senderName' => 'John',
                'type' => 'incoming'
            ])
        );

        $this->assertResponseStatusCodeSame(201);
    }
}
