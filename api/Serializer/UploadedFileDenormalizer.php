<?php

declare(strict_types=1);

namespace Api\Serializer;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class UploadedFileDenormalizer implements DenormalizerInterface
{
    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): UploadedFile
    {
        return $data;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $data instanceof UploadedFile;
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }
}
