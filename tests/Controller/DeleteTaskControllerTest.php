<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeleteTaskControllerTest extends WebTestCase
{
    public function testDeletesTask(): void
    {
        $task = new Task('Delete me');
        (new \ReflectionProperty(Task::class, 'id'))->setValue($task, 1);

        self::assertSame('', $this->deleteTask($task));
    }

    public function testMissingTask(): void
    {
        self::assertSame('{"error":"Task not found."}', $this->deleteTask(null));
    }

    private function deleteTask(?Task $task): string
    {
        $client = static::createClient();
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())->method('find')->with(1)->willReturn($task);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $metadataFactory = $this->createStub(\Doctrine\ORM\Mapping\ClassMetadataFactory::class);
        $metadataFactory->method('isTransient')->willReturnCallback(static fn (string $class): bool => Task::class !== $class);
        $entityManager->method('getMetadataFactory')->willReturn($metadataFactory);
        $entityManager->expects(self::once())->method('getRepository')->with(Task::class)->willReturn($repository);
        $removed = false;
        $entityManager->expects(null === $task ? self::never() : self::once())->method('remove')
            ->with(self::identicalTo($task))
            ->willReturnCallback(static function () use (&$removed): void {
                $removed = true;
            });
        $entityManager->expects(null === $task ? self::never() : self::once())->method('flush')
            ->willReturnCallback(static function () use (&$removed): void {
                self::assertTrue($removed, 'The task must be removed before flushing.');
            });
        // Preserve Doctrine's native lazy-service behavior during kernel shutdown.
        $lazyEntityManager = (new \ReflectionClass($entityManager))->newLazyProxy(static fn () => $entityManager);
        static::getContainer()->set('doctrine.orm.default_entity_manager', $lazyEntityManager);

        try {
            $client->request('DELETE', '/tasks/1');

            self::assertResponseStatusCodeSame(null === $task ? 404 : 204);
            if (null === $task) {
                self::assertResponseHeaderSame('Content-Type', 'application/json');
            }

            return $client->getResponse()->getContent();
        } finally {
            static::$kernel->getContainer()->reset();
        }
    }
}
