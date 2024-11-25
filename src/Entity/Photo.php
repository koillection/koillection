<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\Attribute\Upload;
use App\Entity\Interfaces\CacheableInterface;
use App\Entity\Interfaces\LoggableInterface;
use App\Entity\Interfaces\VisibleInterface;
use App\Entity\Traits\VisibleTrait;
use App\Enum\VisibilityEnum;
use App\Repository\PhotoRepository;
use App\Validator as AppAssert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PhotoRepository::class)]
#[ORM\Table(name: 'koi_photo')]
#[ORM\Index(name: 'idx_photo_final_visibility', columns: ['final_visibility'])]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
        new Post(uriTemplate: '/photos/{id}/image', denormalizationContext: ['groups' => ['photo:image']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Photo image.')),
    ],
    denormalizationContext: ['groups' => ['photo:write']],
    normalizationContext: ['groups' => ['photo:read']]
)]
#[ApiResource(uriTemplate: '/albums/{id}/photos', uriVariables: ['id' => new Link(fromClass: Album::class, fromProperty: 'photos')], normalizationContext: ['groups' => ['photo:read']], operations: [new GetCollection()])]
class Photo implements CacheableInterface, LoggableInterface, VisibleInterface, \Stringable
{
    use VisibleTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['photo:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups(['photo:read', 'photo:write'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['photo:read', 'photo:write'])]
    private ?string $comment = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['photo:read', 'photo:write'])]
    private ?string $place = null;

    #[ORM\ManyToOne(targetEntity: Album::class, inversedBy: 'photos')]
    #[Assert\NotBlank]
    #[Groups(['photo:read', 'photo:write'])]
    private ?Album $album = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['photo:read'])]
    private ?User $owner = null;

    #[Upload(pathProperty: 'image', smallThumbnailPathProperty: 'imageSmallThumbnail')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['photo:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['photo:write', 'photo:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['photo:read'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['photo:read'])]
    private ?string $imageSmallThumbnail = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['photo:read'])]
    private ?\DateTimeImmutable $takenAt = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['photo:read', 'photo:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    #[Groups(['photo:read'])]
    private ?string $parentVisibility = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['photo:read'])]
    private string $finalVisibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['photo:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['photo:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
    }

    #[\Override]
    public function getId(): ?string
    {
        return $this->id;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getTitle() ?? '';
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(?string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getTakenAt(): ?\DateTimeImmutable
    {
        return $this->takenAt;
    }

    public function setTakenAt(?\DateTimeImmutable $takenAt): self
    {
        $this->takenAt = $takenAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;
        $this->setParentVisibility($album->getFinalVisibility());

        return $this;
    }

    #[\Override]
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;
        // Force Doctrine to trigger an update
        if ($file instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageSmallThumbnail(): ?string
    {
        if (null === $this->imageSmallThumbnail) {
            return $this->image;
        }

        return $this->imageSmallThumbnail;
    }

    public function setImageSmallThumbnail(?string $imageSmallThumbnail): self
    {
        $this->imageSmallThumbnail = $imageSmallThumbnail;

        return $this;
    }

    #[\Override]
    public function updateDescendantsVisibility(): self
    {
        return $this;
    }
}
