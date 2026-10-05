<?php

namespace App\Dto;

use App\Entity\Task;

final readonly class UpdateTaskInput
{
    public function __construct(public array $fields)
    {
    }

    public function applyTo(Task $task): void
    {
        foreach ($this->fields as $field => $value) {
            match ($field) {
                'title' => $task->setTitle($value),
                'description' => $task->setDescription($value),
                'completed' => $task->setCompleted($value),
            };
        }
    }
}
