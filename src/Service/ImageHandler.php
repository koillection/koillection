<?php

declare(strict_types=1);

namespace App\Service;

use App\Attribute\Upload;
use App\Enum\ConfigurationEnum;
use App\Repository\ConfigurationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ImageHandler
{
    private readonly PropertyAccessor $accessor;

    public function __construct(
        private readonly RandomStringGenerator $randomStringGenerator,
        private readonly ThumbnailGenerator $thumbnailGenerator,
        private readonly Security $security,
        private readonly ConfigurationRepository $configurationRepository,
        #[Autowire('%kernel.project_dir%/public')] private readonly string $publicPath,
        #[Autowire(param: 'kernel.environment')] private readonly string $env
    ) {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function upload(object $entity, string $property, Upload $attribute): void
    {
        $file = $this->accessor->getValue($entity, $property);

        if ($file instanceof UploadedFile) {
            $user = $this->security->getUser();
            if ($this->env === 'test') {
                $relativePath = '/tmp/';
                $absolutePath = '/tmp/';
            } else {
                $relativePath = 'uploads/' . $user->getId() . '/';
                $absolutePath = $this->publicPath . '/' . $relativePath;
            }

            $generatedName = $this->randomStringGenerator->generate(20);
            $extension = $file->guessExtension();
            $fileName = $generatedName . '.' . $extension;
            $file->move($absolutePath, $fileName);

            $this->removeOldFile($entity, $attribute);
            $this->accessor->setValue($entity, $attribute->getPathProperty(), $relativePath . $fileName);

            if ($attribute->getMaxWidth() || $attribute->getMaxHeight()) {
                $this->thumbnailGenerator->crop($absolutePath . '/' . $fileName, $attribute->getMaxWidth(), $attribute->getMaxHeight());
            }

            $thumbnailFormat = $this->configurationRepository->findOneBy(['label' => ConfigurationEnum::THUMBNAILS_FORMAT])?->getValue() ?? $extension;
            if (null !== $attribute->getSmallThumbnailPathProperty()) {
                $smallThumbnailFileName = $generatedName . '_small.' . $thumbnailFormat;
                $result = $this->thumbnailGenerator->generate($absolutePath . '/' . $fileName, $absolutePath . '/' . $smallThumbnailFileName, 300, $thumbnailFormat);
                $this->accessor->setValue($entity, $attribute->getSmallThumbnailPathProperty(), $result ? $relativePath . $smallThumbnailFileName : null);
            }

            if (null !== $attribute->getLargeThumbnailPathProperty()) {
                $largeThumbnailFileName = $generatedName . '_large.' . $thumbnailFormat;
                $result = $this->thumbnailGenerator->generate($absolutePath . '/' . $fileName, $absolutePath . '/' . $largeThumbnailFileName, 600, $thumbnailFormat);
                $this->accessor->setValue($entity, $attribute->getLargeThumbnailPathProperty(), $result ? $relativePath . $largeThumbnailFileName : null);
            }

            if (null !== $attribute->getOriginalFilenamePathProperty()) {
                $this->accessor->setValue($entity, $attribute->getOriginalFilenamePathProperty(), $file->getClientOriginalName());
            }

            $this->accessor->setValue($entity, $property, null);
        }
    }

    public function setFileFromFilename(object $entity, string $property, Upload $attribute): void
    {
        $path = $this->accessor->getValue($entity, $attribute->getPathProperty());

        if (null !== $path) {
            $file = new File($this->publicPath . '/' . $path, false);
            $this->accessor->setValue($entity, $property, $file);
        }
    }

    public function removeOldFile(object $entity, Upload $attribute): void
    {
        if (null !== $attribute->getPathProperty()) {
            $path = $this->accessor->getValue($entity, $attribute->getPathProperty());
            if (null !== $path) {
                @unlink(str_starts_with($path, '/tmp') ? $path : $this->publicPath . '/' . $path);
            }

            $this->accessor->setValue($entity, $attribute->getPathProperty(), null);
        }

        if (null !== $attribute->getSmallThumbnailPathProperty()) {
            $path = $this->accessor->getValue($entity, $attribute->getSmallThumbnailPathProperty());
            if (null !== $path) {
                @unlink(str_starts_with($path, '/tmp') ? $path : $this->publicPath . '/' . $path);
            }

            $this->accessor->setValue($entity, $attribute->getSmallThumbnailPathProperty(), null);
        }

        if (null !== $attribute->getLargeThumbnailPathProperty()) {
            $path = $this->accessor->getValue($entity, $attribute->getLargeThumbnailPathProperty());
            if (null !== $path) {
                @unlink(str_starts_with($path, '/tmp') ? $path : $this->publicPath . '/' . $path);
            }

            $this->accessor->setValue($entity, $attribute->getLargeThumbnailPathProperty(), null);
        }
    }
}
