<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

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

        $this->client->request('GET', '/api/messages/' . $id);

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame($id, $responseData['id']);
        $this->assertSame('Single Message', $responseData['subject']);
    }

    public function testUpdateMessage(): void
    {
        $this->client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'Original',
                'message' => 'Original message',
                'date' => '2025-01-01 10:00:00',
                'senderName' => 'John',
                'type' => 'incoming'
            ])
        );

        $created = json_decode($this->client->getResponse()->getContent(), true);
        $id = $created['id'];

        $this->client->request(
            'PUT',
            '/api/messages/' . $id,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'Updated',
                'message' => 'Updated message',
                'date' => '2025-01-02 12:00:00',
                'senderName' => 'Jane',
                'type' => 'outgoing'
            ])
        );

        $this->assertResponseIsSuccessful();

        $updated = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertSame('Updated', $updated['subject']);
        $this->assertSame('outgoing', $updated['type']);

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $message = $entityManager
            ->getRepository(\App\Entity\Message::class)
            ->find($id);

        $this->assertSame('Updated', $message->getSubject());
    }

    public function testDeleteMessage(): void
    {
        $this->client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'To Delete',
                'message' => 'Delete me',
                'date' => '2025-01-01 10:00:00',
                'senderName' => 'John',
                'type' => 'incoming'
            ])
        );

        $created = json_decode($this->client->getResponse()->getContent(), true);
        $id = $created['id'];

        $this->client->request('DELETE', '/api/messages/' . $id);

        $this->assertResponseStatusCodeSame(204);

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $message = $entityManager
            ->getRepository(\App\Entity\Message::class)
            ->find($id);

        $this->assertNull($message);
    }

    public function testMessageIsNotProcessedOnCreate(): void
    {
        $this->client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'Not processed yet',
                'message' => 'Test',
                'date' => '2025-01-01 10:00:00',
                'senderName' => 'John',
                'type' => 'incoming'
            ])
        );

        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $id = $data['id'];

        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $message = $entityManager
            ->getRepository(\App\Entity\Message::class)
            ->find($id);

        $this->assertNotNull($message);
        $this->assertNull($message->getProcessedAt());
    }

    public function testProcessCommandProcessesMessages(): void
    {
        $this->client->request(
            'POST',
            '/api/messages',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'subject' => 'Process me',
                'message' => 'Test',
                'date' => '2025-01-01 10:00:00',
                'senderName' => 'John',
                'type' => 'incoming'
            ])
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $id = $data['id'];

        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $command = $application->find('app:process-messages');

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->clear();

        $message = $entityManager
            ->getRepository(\App\Entity\Message::class)
            ->find($id);

        $this->assertNotNull($message->getProcessedAt());
    }
}
