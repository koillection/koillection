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
use App\Entity\Interfaces\CacheableInterface;
use App\Entity\Interfaces\LoggableInterface;
use App\Entity\Interfaces\VisibleInterface;
use App\Entity\Traits\VisibleTrait;
use App\Enum\VisibilityEnum;
use App\Repository\WishlistRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WishlistRepository::class)]
#[ORM\Table(name: 'koi_wishlist')]
#[ORM\Index(name: 'idx_wishlist_final_visibility', columns: ['final_visibility'])]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
        new Post(uriTemplate: '/wishlists/{id}/image', denormalizationContext: ['groups' => ['wishlist:image']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Wishlist image.')),
    ],
    denormalizationContext: ['groups' => ['wishlist:write']],
    normalizationContext: ['groups' => ['wishlist:read']]
)]
#[ApiResource(uriTemplate: '/wishes/{id}/wishlist', uriVariables: ['id' => new Link(fromClass: Wish::class, fromProperty: 'wishlist')], normalizationContext: ['groups' => ['wishlist:read']], operations: [new Get()])]
#[ApiResource(uriTemplate: '/wishlists/{id}/children', uriVariables: ['id' => new Link(fromClass: Wishlist::class, fromProperty: 'children')], normalizationContext: ['groups' => ['wishlist:read']], operations: [new GetCollection()])]
#[ApiResource(uriTemplate: '/wishlists/{id}/parent', uriVariables: ['id' => new Link(fromClass: Wishlist::class, fromProperty: 'parent')], normalizationContext: ['groups' => ['wishlist:read']], operations: [new Get()])]
class Wishlist implements BreadcrumbableInterface, CacheableInterface, LoggableInterface, VisibleInterface, \Stringable
{
    use VisibleTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['wishlist:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['wishlist:read', 'wishlist:write'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wishlists')]
    #[Groups(['wishlist:read'])]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: Wish::class, mappedBy: 'wishlist', cascade: ['all'])]
    #[ORM\OrderBy(['name' => Order::Ascending->value])]
    private DoctrineCollection $wishes;

    #[ORM\Column(type: Types::STRING, length: 6)]
    #[Groups(['wishlist:read'])]
    private ?string $color = null;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToMany(targetEntity: Wishlist::class, mappedBy: 'parent', cascade: ['all'])]
    #[ORM\OrderBy(['name' => Order::Ascending->value])]
    private DoctrineCollection $children;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: Wishlist::class, inversedBy: 'children')]
    #[Groups(['wishlist:read', 'wishlist:write'])]
    #[Assert\Expression('not (value == this)', message: 'error.parent.same_as_current_object')]
    private ?Wishlist $parent = null;

    #[Upload(pathProperty: 'image', deleteProperty: 'deleteImage', maxWidth: 200, maxHeight: 200)]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['wishlist:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['wishlist:write', 'wishlist:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['wishlist:read'])]
    private ?string $image = null;

    #[Groups(['wishlist:write'])]
    private ?bool $deleteImage = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['wishlist:read'])]
    private int $seenCounter = 0;

    #[ORM\Column(type: Types::JSON)]
    private ?array $cachedValues = [];

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToOne(targetEntity: DisplayConfiguration::class, cascade: ['all'])]
    private DisplayConfiguration $childrenDisplayConfiguration;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['wishlist:read', 'wishlist:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    #[Groups(['wishlist:read'])]
    private ?string $parentVisibility = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['wishlist:read'])]
    private string $finalVisibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['wishlist:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['wishlist:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Assert\IsFalse(message: 'error.parent.same_as_current_object')]
    private bool $hasParentEqualToItself = false;

    #[Assert\IsFalse(message: 'error.parent.is_child_of_current_object')]
    private bool $hasParentEqualToOneOfItsChildren = false;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->wishes = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->childrenDisplayConfiguration = new DisplayConfiguration();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    #[\Override]
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

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

    public function getWishes(): DoctrineCollection
    {
        return $this->wishes;
    }

    public function getChildren(): DoctrineCollection
    {
        return $this->children;
    }

    public function getChildrenRecursively(): array
    {
        $children = [];

        foreach ($this->children as $child) {
            $children[] = $child;
            $children = array_merge($children, $child->getChildrenRecursively());
        }

        return $children;
    }

    public function getParent(): ?self
    {
        // Protection against infinite loops
        if ($this->parent === $this) {
            return null;
        }

        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        // Protections against infinite loops
        if ($parent === $this) {
            $this->hasParentEqualToItself = true;

            return $this;
        }

        if (\in_array($parent, $this->getChildrenRecursively())) {
            $this->hasParentEqualToOneOfItsChildren = true;

            return $this;
        }

        $this->parent = $parent;
        $this->setParentVisibility($parent?->getFinalVisibility());

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

    public function getChildrenDisplayConfiguration(): DisplayConfiguration
    {
        return $this->childrenDisplayConfiguration;
    }

    public function getCachedValues(): array
    {
        return $this->cachedValues;
    }

    public function setCachedValues(array $cachedValues): Wishlist
    {
        $this->cachedValues = $cachedValues;

        return $this;
    }

    public function getDeleteImage(): ?bool
    {
        return $this->deleteImage;
    }

    public function setDeleteImage(?bool $deleteImage): Wishlist
    {
        $this->deleteImage = $deleteImage;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    #[\Override]
    public function updateDescendantsVisibility(): self
    {
        foreach ($this->getWishes() as $wish) {
            $wish->setParentVisibility($this->finalVisibility);
        }

        foreach ($this->getChildren() as $child) {
            $child->setParentVisibility($this->finalVisibility);
        }

        return $this;
    }
}
