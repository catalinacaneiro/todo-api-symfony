<?php

namespace App\Controller;

use App\Dto\CreateTaskInput;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'task_create', methods: ['POST'], format: 'json')]
    public function create(
        #[MapRequestPayload(acceptFormat: 'json')] CreateTaskInput $input,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): JsonResponse {
        $task = new Task($input->title);
        $task->setDescription($input->description);

        $violations = $validator->validate($task);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }

            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->json([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'completed' => $task->isCompleted(),
            'createdAt' => $task->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], Response::HTTP_CREATED);
    }

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
