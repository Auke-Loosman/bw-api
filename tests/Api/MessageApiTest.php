<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MessageApiTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        static::ensureKernelShutdown();
        $this->client = static::createClient();

        $entityManager = static::getContainer()
            ->get('doctrine')
            ->getManager();

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testCreateMessage(): void
    {
        $payload = [
            'subject' => 'Test Subject',
            'message' => 'Test Message',
            'date' => '2025-01-01 10:00:00',
            'senderName' => 'John',
            'type' => 'incoming'
        ];

        $this->client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Test Subject', $data['subject']);

        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $message = $entityManager
            ->getRepository(\App\Entity\Message::class)
            ->find($data['id']);

        $this->assertNotNull($message);
        $this->assertSame('Test Subject', $message->getSubject());
    }

    public function testGetAllMessages(): void
    {
        $this->client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'Message 1',
                'message' => 'Test',
                'date' => '2025-01-01 10:00:00',
                'senderName' => 'John',
                'type' => 'incoming'
            ])
        );

        $this->client->request('GET', '/api/messages');

        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertSame('Message 1', $data[0]['subject']);
    }

        public function testGetSingleMessage(): void
    {

        $this->client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'Single Message',
                'message' => 'Test',
                'date' => '2025-01-01 10:00:00',
                'senderName' => 'John',
                'type' => 'incoming'
            ])
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $id = $data['id'];

        // Now fetch it by ID
        $this->client->request('GET', '/api/messages/' . $id);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($id, $responseData['id']);
        $this->assertSame('Single Message', $responseData['subject']);
    }
}
