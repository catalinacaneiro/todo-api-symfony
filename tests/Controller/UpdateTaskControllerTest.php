<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UpdateTaskControllerTest extends WebTestCase
{
    #[DataProvider('updates')]
    public function testPartialUpdates(string $body, array $changes): void
    {
        $task = $this->task();
        $response = $this->patch($body, $task, 200);
        self::assertSame(array_replace([
            'id' => 1, 'title' => 'Original', 'description' => 'Description',
            'completed' => false, 'createdAt' => '2026-10-04T18:00:00+00:00',
        ], $changes), $response);
        self::assertSame($response['title'], $task->getTitle());
        self::assertSame($response['description'], $task->getDescription());
        self::assertSame($response['completed'], $task->isCompleted());
    }

    public static function updates(): iterable
    {
        yield ['{"completed":true}', ['completed' => true]];
        yield ['{"title":"Updated"}', ['title' => 'Updated']];
        yield ['{"description":null}', ['description' => null]];
        yield ['{"title":"Updated","description":"New","completed":true}', ['title' => 'Updated', 'description' => 'New', 'completed' => true]];
        yield ['{"completed":false}', ['completed' => false]];
    }

    #[DataProvider('invalidBodies')]
    public function testInvalidRequestsDoNotChangeTheTask(string $body, int $status): void
    {
        $task = $this->task();
        $this->patch($body, $task, $status);
        self::assertSame('Original', $task->getTitle());
        self::assertSame('Description', $task->getDescription());
        self::assertFalse($task->isCompleted());
    }

    public static function invalidBodies(): iterable
    {
        yield ['{"title":"","description":null,"completed":true}', 422];
        yield ['{"title":"   "}', 422];
        yield [json_encode(['title' => str_repeat('a', 256)]), 422];
        foreach (['{}', '[]', '[1]', 'null', 'true', '"title"', '42', '{', '', '{"unknown":true}', '{"title":1}', '{"title":null}', '{"description":false}', '{"description":{}}', '{"completed":1}', '{"completed":"false"}', '{"completed":null}'] as $body) {
            yield [$body, 400];
        }
    }

    public function testMissingTask(): void
    {
        self::assertSame(['error' => 'Task not found.'], $this->patch('{"completed":true}', null, 404));
    }

    private function patch(string $body, ?Task $task, int $status): array
    {
        $client = static::createClient();
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(400 === $status ? self::never() : self::once())->method('find')->with(1)->willReturn($task);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $metadataFactory = $this->createStub(\Doctrine\ORM\Mapping\ClassMetadataFactory::class);
        $metadataFactory->method('isTransient')->willReturnCallback(static fn (string $class): bool => Task::class !== $class);
        $entityManager->method('getMetadataFactory')->willReturn($metadataFactory);
        $entityManager->expects(400 === $status ? self::never() : self::once())->method('getRepository')->with(Task::class)->willReturn($repository);
        $entityManager->expects(200 === $status ? self::once() : self::never())->method('flush');
        $lazyEntityManager = (new \ReflectionClass($entityManager))->newLazyProxy(static fn () => $entityManager);
        static::getContainer()->set('doctrine.orm.default_entity_manager', $lazyEntityManager);
        try {
            $client->request('PATCH', '/tasks/1', server: ['CONTENT_TYPE' => 'application/json'], content: $body);
            self::assertResponseStatusCodeSame($status);
            self::assertResponseHeaderSame('Content-Type', 'application/json');

            return json_decode($client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } finally {
            static::$kernel->getContainer()->reset();
        }
    }

    private function task(): Task
    {
        $task = (new Task('Original'))->setDescription('Description');
        (new \ReflectionProperty(Task::class, 'id'))->setValue($task, 1);
        (new \ReflectionProperty(Task::class, 'createdAt'))->setValue($task, new \DateTimeImmutable('2026-10-04T18:00:00+00:00'));

        return $task;
    }
}
