<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $tasks = $entityManager->getRepository(Task::class)->findBy([], [
            'createdAt' => 'DESC',
            'id' => 'DESC',
        ]);

        return $this->json(array_map(static fn (Task $task): array => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'completed' => $task->isCompleted(),
            'createdAt' => $task->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $tasks));
    }
}
