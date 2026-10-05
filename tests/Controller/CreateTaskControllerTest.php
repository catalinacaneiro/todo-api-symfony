-.<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CreateTaskControllerTest extends WebTestCase
{
    #[DataProvider('validPayloads')]
    public function testCreatesTask(array $payload): void
    {
        $before = new \DateTimeImmutable();
        $task = null;
        $response = $this->postTask($payload, true, $task);

        self::assertInstanceOf(Task::class, $task);
        self::assertSame([
            'id' => 42,
            'title' => $payload['title'],
            'description' => $payload['description'] ?? null,
            'completed' => false,
            'createdAt' => $task->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $response);
        self::assertGreaterThanOrEqual($before, $task->getCreatedAt());
        self::assertLessThanOrEqual(new \DateTimeImmutable(), $task->getCreatedAt());
    }

    public static function validPayloads(): iterable
    {
        yield 'with description' => [['title' => 'Learn Symfony', 'description' => 'Build the POST endpoint']];
        yield 'without description' => [['title' => 'Learn Symfony']];
        yield 'maximum title length' => [['title' => str_repeat('a', 255)]];
        yield 'server owns defaults' => [['title' => 'Learn Symfony', 'completed' => true, 'createdAt' => '2000-01-01T00:00:00+00:00', 'id' => 999]];
    }

    #[DataProvider('invalidPayloads')]
    public function testInvalidTaskIsNotSaved(array $payload): void
    {
        $task = null;
        $response = $this->postTask($payload, false, $task);

        self::assertNotEmpty($response['errors']);
        self::assertSame('title', $response['errors'][0]['field']);
        self::assertNull($task);
    }

    public static function invalidPayloads(): iterable
    {
        yield 'missing title' => [['description' => 'No title']];
        yield 'empty title' => [['title' => '']];
        yield 'whitespace title' => [['title' => " \t\n "]];
        yield 'too long' => [['title' => str_repeat('a', 256)]];
    }

    #[DataProvider('invalidTypes')]
    public function testInvalidTypesAreNotSaved(array $payload): void
    {
        $task = null;
        $response = $this->postTask($payload, false, $task);

        self::assertNotEmpty($response);
        self::assertNull($task);
    }

    public static function invalidTypes(): iterable
    {
        yield 'null title' => [['title' => null]];
        yield 'array title' => [['title' => []]];
        yield 'array description' => [['title' => 'Learn Symfony', 'description' => []]];
    }

    private function postTask(array $payload, bool $valid, ?Task &$task): array
    {
        $client = static::createClient();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($valid ? self::once() : self::never())->method('persist')
            ->willReturnCallback(static function (Task $entity) use (&$task): void {
                $task = $entity;
                self::assertNull($task->getId());
            });
        $entityManager->expects($valid ? self::once() : self::never())->method('flush')
            ->willReturnCallback(static function () use (&$task): void {
                self::assertInstanceOf(Task::class, $task);
                (new \ReflectionProperty(Task::class, 'id'))->setValue($task, 42);
            });
        $lazyEntityManager = (new \ReflectionClass($entityManager))->newLazyProxy(static fn () => $entityManager);
        static::getContainer()->set('doctrine.orm.default_entity_manager', $lazyEntityManager);

        try {
            $client->jsonRequest('POST', '/tasks', $payload);

            self::assertResponseStatusCodeSame($valid ? 201 : 422);
            self::assertResponseHeaderSame('Content-Type', 'application/json');

            return json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            static::$kernel->getContainer()->reset();
        }
    }
}
