<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    public function testTasksAreReturnedWithOnlyThePublicFields(): void
    {
        $newer = $this->task(3, 'Newest', '2026-10-04T18:00:00+00:00');
        $newer->setDescription('A description')->setCompleted(true);
        $sameTime = $this->task(2, 'Same time', '2026-10-04T18:00:00+00:00');
        $older = $this->task(1, 'Older', '2026-10-03T18:00:00+00:00');

        $response = $this->requestTasks([$newer, $sameTime, $older]);

        self::assertSame([
            ['id' => 3, 'title' => 'Newest', 'description' => 'A description', 'completed' => true, 'createdAt' => '2026-10-04T18:00:00+00:00'],
            ['id' => 2, 'title' => 'Same time', 'description' => null, 'completed' => false, 'createdAt' => '2026-10-04T18:00:00+00:00'],
            ['id' => 1, 'title' => 'Older', 'description' => null, 'completed' => false, 'createdAt' => '2026-10-03T18:00:00+00:00'],
        ], json_decode($response, true, 512, JSON_THROW_ON_ERROR));
    }

    public function testNoTasksReturnsAnEmptyJsonArray(): void
    {
        self::assertSame('[]', $this->requestTasks([]));
    }

    private function requestTasks(array $tasks): string
    {
        $client = static::createClient();
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('findBy')
            ->with([], ['createdAt' => 'DESC', 'id' => 'DESC'])
            ->willReturn($tasks);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('getRepository')
            ->with(Task::class)->willReturn($repository);
        $container = static::getContainer();
        // Preserve Doctrine's native lazy-service behavior during kernel shutdown.
        $lazyEntityManager = (new \ReflectionClass($entityManager))->newLazyProxy(static fn () => $entityManager);
        $container->set('doctrine.orm.default_entity_manager', $lazyEntityManager);

        try {
            $client->request('GET', '/tasks');

            self::assertResponseStatusCodeSame(200);
            self::assertResponseHeaderSame('Content-Type', 'application/json');

            return $client->getResponse()->getContent();
        } finally {
            // Remove the test double before the kernel resets Doctrine again.
            static::$kernel->getContainer()->reset();
        }
    }

    private function task(int $id, string $title, string $createdAt): Task
    {
        $task = new Task($title);
        (new \ReflectionProperty(Task::class, 'id'))->setValue($task, $id);
        (new \ReflectionProperty(Task::class, 'createdAt'))->setValue($task, new \DateTimeImmutable($createdAt));

        return $task;
    }
}
