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
use App\Repository\CollectionRepository;
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

#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Table(name: 'koi_collection')]
#[ORM\Index(name: 'idx_collection_final_visibility', columns: ['final_visibility'])]
#[ApiResource(
    denormalizationContext: ['groups' => ['collection:write']],
    normalizationContext: ['groups' => ['collection:read']],
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
        new Post(uriTemplate: '/collections/{id}/image', denormalizationContext: ['groups' => ['collection:image']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Collection image.'))
    ]
)]
#[ApiResource(uriTemplate: '/collections/{id}/children', uriVariables: ['id' => new Link(fromClass: Collection::class, fromProperty: 'children')], normalizationContext: ['groups' => ['collection:read']], operations: [new GetCollection()])]
#[ApiResource(uriTemplate: '/collections/{id}/parent', uriVariables: ['id' => new Link(fromClass: Collection::class, fromProperty: 'parent')], normalizationContext: ['groups' => ['collection:read']], operations: [new Get()])]
#[ApiResource(uriTemplate: '/data/{id}/collection', uriVariables: ['id' => new Link(fromClass: Datum::class, fromProperty: 'collection')], normalizationContext: ['groups' => ['collection:read']], operations: [new Get()])]
#[ApiResource(uriTemplate: '/items/{id}/collection', uriVariables: ['id' => new Link(fromClass: Item::class, fromProperty: 'collection')], normalizationContext: ['groups' => ['collection:read']], operations: [new Get()])]
class Collection implements LoggableInterface, BreadcrumbableInterface, CacheableInterface, VisibleInterface, \Stringable
{
    use VisibleTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['collection:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups(['collection:read', 'collection:write'])]
    private ?string $title = null;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToMany(targetEntity: Collection::class, mappedBy: 'parent', cascade: ['all'])]
    #[ORM\OrderBy(['title' => Order::Ascending->value])]
    private DoctrineCollection $children;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToOne(targetEntity: DisplayConfiguration::class, cascade: ['all'])]
    private DisplayConfiguration $childrenDisplayConfiguration;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToOne(targetEntity: Collection::class, inversedBy: 'children')]
    #[Groups(['collection:read', 'collection:write'])]
    private ?Collection $parent = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'collections')]
    #[Groups(['collection:read'])]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: Item::class, mappedBy: 'collection', cascade: ['all'])]
    private DoctrineCollection $items;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToOne(targetEntity: DisplayConfiguration::class, cascade: ['all'])]
    private DisplayConfiguration $itemsDisplayConfiguration;

    #[ORM\OneToMany(targetEntity: Datum::class, mappedBy: 'collection', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => Order::Ascending->value])]
    #[AppAssert\UniqueDatumLabel]
    private DoctrineCollection $data;

    #[ORM\Column(type: Types::STRING, length: 6)]
    #[Groups(['collection:read'])]
    private ?string $color = null;

    #[Upload(pathProperty: 'image', deleteProperty: 'deleteImage', maxWidth: 200, maxHeight: 200)]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['colletion:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['collection:write', 'collection:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['collection:read'])]
    private ?string $image = null;

    #[Groups(['collection:write'])]
    private ?bool $deleteImage = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['collection:read'])]
    private int $seenCounter = 0;

    #[ORM\Column(type: Types::JSON)]
    private ?array $cachedValues = [];

    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(['collection:read', 'collection:write'])]
    private ?Template $itemsDefaultTemplate = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['collection:read', 'collection:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    #[Groups(['collection:read'])]
    private ?string $parentVisibility = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['collection:read'])]
    private ?string $finalVisibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['collection:read'])]
    private ?string $scrapedFromUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['collection:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['collection:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    private ?string $orderingValue = null;

    #[Assert\IsFalse(message: 'error.parent.same_as_current_object')]
    private bool $hasParentEqualToItself = false;

    #[Assert\IsFalse(message: 'error.parent.is_child_of_current_object')]
    private bool $hasParentEqualToOneOfItsChildren = false;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->children = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->data = new ArrayCollection();
        $this->childrenDisplayConfiguration = new DisplayConfiguration();
        $this->itemsDisplayConfiguration = new DisplayConfiguration();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getTitle() ?? '';
    }

    public function getDatumByLabel(string $label): ?Datum
    {
        foreach ($this->getData() as $datum) {
            if ($datum->getLabel() === $label) {
                return $datum;
            }
        }

        return null;
    }

    #[\Override]
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): Collection
    {
        $this->title = $title;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): Collection
    {
        $this->color = $color;

        return $this;
    }

    public function getSeenCounter(): ?int
    {
        return $this->seenCounter;
    }

    public function setSeenCounter(int $seenCounter): Collection
    {
        $this->seenCounter = $seenCounter;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): Collection
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): Collection
    {
        $this->updatedAt = $updatedAt;

        return $this;
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

    public function getParent(): ?Collection
    {
        return $this->parent;
    }

    public function setParent(?Collection $parent): Collection
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

    #[\Override]
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): Collection
    {
        $this->owner = $owner;

        return $this;
    }

    public function getItems(): DoctrineCollection
    {
        return $this->items;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): Collection
    {
        $this->image = $image;

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): Collection
    {
        $this->file = $file;
        // Force Doctrine to trigger an update
        if ($file instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getData(): DoctrineCollection
    {
        return $this->data;
    }

    public function addData(Datum $data): Collection
    {
        if (!$this->data->contains($data)) {
            $this->data[] = $data;
            $data->setCollection($this);
        }

        return $this;
    }

    public function removeData(Datum $data): Collection
    {
        if ($this->data->contains($data)) {
            $this->data->removeElement($data);
            // set the owning side to null (unless already changed)
            if ($data->getCollection() === $this) {
                $data->setCollection(null);
            }
        }

        return $this;
    }

    public function getItemsDefaultTemplate(): ?Template
    {
        return $this->itemsDefaultTemplate;
    }

    public function setItemsDefaultTemplate(?Template $itemsDefaultTemplate): Collection
    {
        $this->itemsDefaultTemplate = $itemsDefaultTemplate;

        return $this;
    }

    public function getChildrenDisplayConfiguration(): DisplayConfiguration
    {
        return $this->childrenDisplayConfiguration;
    }

    public function getItemsDisplayConfiguration(): DisplayConfiguration
    {
        return $this->itemsDisplayConfiguration;
    }

    public function getOrderingValue(): ?string
    {
        return $this->orderingValue;
    }

    public function setOrderingValue(?string $orderingValue): Collection
    {
        $this->orderingValue = $orderingValue;

        return $this;
    }

    public function getCachedValues(): array
    {
        return $this->cachedValues;
    }

    public function setCachedValues(array $cachedValues): Collection
    {
        $this->cachedValues = $cachedValues;

        return $this;
    }

    public function getDeleteImage(): ?bool
    {
        return $this->deleteImage;
    }

    public function setDeleteImage(?bool $deleteImage): Collection
    {
        $this->deleteImage = $deleteImage;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getScrapedFromUrl(): ?string
    {
        return $this->scrapedFromUrl;
    }

    public function setScrapedFromUrl(?string $scrapedFromUrl): Collection
    {
        $this->scrapedFromUrl = $scrapedFromUrl;

        return $this;
    }

    #[\Override]
    public function updateDescendantsVisibility(): self
    {
        foreach ($this->getData() as $datum) {
            $datum->setParentVisibility($this->finalVisibility);
        }

        foreach ($this->getItems() as $item) {
            $item->setParentVisibility($this->finalVisibility);
        }

        foreach ($this->getChildren() as $child) {
            $child->setParentVisibility($this->finalVisibility);
        }

        return $this;
    }
}
