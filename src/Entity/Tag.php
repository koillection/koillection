<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
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
use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Entity\Interfaces\LoggableInterface;
use App\Enum\VisibilityEnum;
use App\Repository\TagRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'koi_tag')]
#[ORM\Index(name: 'idx_tag_visibility', columns: ['visibility'])]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
        new Post(uriTemplate: '/tags/{id}/image', denormalizationContext: ['groups' => ['tag:image']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Tag image.')),
    ],
    denormalizationContext: ['groups' => ['tag:write']],
    normalizationContext: ['groups' => ['tag:read']]
)]
#[ApiResource(uriTemplate: '/items/{id}/tags', uriVariables: ['id' => new Link(fromClass: Item::class, fromProperty: 'tags')], normalizationContext: ['groups' => ['tag:read']], operations: [new GetCollection()])]
#[ApiResource(uriTemplate: '/tag_categories/{id}/tags', uriVariables: ['id' => new Link(fromClass: TagCategory::class, fromProperty: 'tags')], normalizationContext: ['groups' => ['tag:read']], operations: [new GetCollection()])]
class Tag implements BreadcrumbableInterface, LoggableInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['tag:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['tag:read', 'tag:write'])]
    #[Assert\NotBlank]
    private string $label;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['tag:read', 'tag:write'])]
    private ?string $description = null;

    #[Upload(pathProperty: 'image', smallThumbnailPathProperty: 'imageSmallThumbnail')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['tag:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['tag:write', 'tag:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['tag:read'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['tag:read'])]
    private ?string $imageSmallThumbnail = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tags')]
    #[Groups(['tag:read'])]
    private ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: TagCategory::class, inversedBy: 'tags', fetch: 'EAGER', cascade: ['persist'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['tag:read', 'tag:write'])]
    private ?TagCategory $category = null;

    #[ORM\ManyToMany(targetEntity: Item::class, mappedBy: 'tags')]
    private DoctrineCollection $items;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['tag:read'])]
    private int $seenCounter = 0;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToOne(targetEntity: DisplayConfiguration::class, cascade: ['all'])]
    private ?DisplayConfiguration $itemsDisplayConfiguration;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['tag:read', 'tag:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['tag:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['tag:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->items = new ArrayCollection();
        $this->itemsDisplayConfiguration = new DisplayConfiguration();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getLabel() ?? '';
    }

    #[\Override]
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSeenCounter(): ?int
    {
        return $this->seenCounter;
    }

    public function setSeenCounter(int $seenCounter): self
    {
        $this->seenCounter = $seenCounter;

        return $this;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        $this->visibility = $visibility;

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
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCategory(): ?TagCategory
    {
        return $this->category;
    }

    public function setCategory(?TagCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getItems(): DoctrineCollection
    {
        return $this->items;
    }

    public function getItemsDisplayConfiguration(): ?DisplayConfiguration
    {
        return $this->itemsDisplayConfiguration;
    }
}
