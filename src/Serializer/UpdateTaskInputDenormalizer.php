<?php

namespace App\Serializer;

use App\Dto\UpdateTaskInput;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class UpdateTaskInputDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): UpdateTaskInput
    {
        if (!$data instanceof \stdClass || [] === $fields = get_object_vars($data)) {
            throw new BadRequestHttpException('Supply a non-empty JSON object.');
        }

        foreach ($fields as $field => $value) {
            $valid = match ($field) {
                'title' => is_string($value),
                'description' => null === $value || is_string($value),
                'completed' => is_bool($value),
                default => false,
            };
            if (!$valid) {
                throw new BadRequestHttpException(sprintf('Unknown field or invalid type: %s.', $field));
            }
        }

        return new UpdateTaskInput($fields);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return UpdateTaskInput::class === $type;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [UpdateTaskInput::class => true];
    }
}
