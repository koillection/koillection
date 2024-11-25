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
use App\Enum\DatumTypeEnum;
use App\Enum\VisibilityEnum;
use App\Repository\ItemRepository;
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

#[ORM\Entity(repositoryClass: ItemRepository::class)]
#[ORM\Table(name: 'koi_item')]
#[ORM\Index(name: 'idx_item_final_visibility', columns: ['final_visibility'])]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
        new Post(uriTemplate: '/items/{id}/image', denormalizationContext: ['groups' => ['item:image']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Item image.'))
    ],
    denormalizationContext: ['groups' => ['item:write']],
    normalizationContext: ['groups' => ['item:read']]
)]
#[ApiResource(uriTemplate: '/collections/{id}/items', uriVariables: ['id' => new Link(fromClass: Collection::class, fromProperty: 'items')], normalizationContext: ['groups' => ['item:read']], operations: [new GetCollection()])]
#[ApiResource(uriTemplate: '/data/{id}/item', uriVariables: ['id' => new Link(fromClass: Datum::class, fromProperty: 'item')], normalizationContext: ['groups' => ['item:read']], operations: [new Get()])]
#[ApiResource(uriTemplate: '/items/{id}/related_items', uriVariables: ['id' => new Link(fromClass: Item::class, fromProperty: 'relatedItems')], normalizationContext: ['groups' => ['item:read']], operations: [new GetCollection()])]
#[ApiResource(uriTemplate: '/loans/{id}/item', uriVariables: ['id' => new Link(fromClass: Loan::class, fromProperty: 'item')], normalizationContext: ['groups' => ['item:read']], operations: [new Get()])]
#[ApiResource(uriTemplate: '/tags/{id}/items', uriVariables: ['id' => new Link(fromClass: Tag::class, fromProperty: 'items', toProperty: 'tags')], normalizationContext: ['groups' => ['item:read']], operations: [new GetCollection()])]
class Item implements BreadcrumbableInterface, LoggableInterface, CacheableInterface, VisibleInterface, \Stringable
{
    use VisibleTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['item:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Groups(['item:read', 'item:write'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\GreaterThanOrEqual(1)]
    #[Groups(['item:read', 'item:write'])]
    private int $quantity = 1;

    #[ORM\ManyToOne(targetEntity: Collection::class, inversedBy: 'items')]
    #[Assert\NotBlank]
    #[Groups(['item:read', 'item:write'])]
    private ?Collection $collection = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['item:read'])]
    private ?User $owner = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'items', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'koi_item_tag')]
    #[ORM\JoinColumn(name: 'item_id')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id')]
    #[ORM\OrderBy(['label' => Order::Ascending->value])]
    #[Groups(['item:write'])]
    private DoctrineCollection $tags;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\ManyToMany(targetEntity: Item::class, inversedBy: 'relatedTo')]
    #[ORM\JoinTable(name: 'koi_item_related_item')]
    #[ORM\JoinColumn(name: 'item_id')]
    #[ORM\InverseJoinColumn(name: 'related_item_id', referencedColumnName: 'id')]
    #[ORM\OrderBy(['name' => Order::Ascending->value])]
    #[Groups(['item:write'])]
    private DoctrineCollection $relatedItems;

    #[ORM\ManyToMany(targetEntity: Item::class, mappedBy: 'relatedItems')]
    #[ORM\OrderBy(['name' => Order::Ascending->value])]
    private DoctrineCollection $relatedTo;

    #[ORM\OneToMany(targetEntity: Datum::class, mappedBy: 'item', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => Order::Ascending->value])]
    #[AppAssert\UniqueDatumLabel]
    private DoctrineCollection $data;

    #[ORM\OneToMany(targetEntity: Loan::class, mappedBy: 'item', cascade: ['remove'])]
    private DoctrineCollection $loans;

    #[Upload(pathProperty: 'image', smallThumbnailPathProperty: 'imageSmallThumbnail', largeThumbnailPathProperty: 'imageLargeThumbnail')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['item:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['item:write', 'item:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['item:read'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['item:read'])]
    private ?string $imageSmallThumbnail = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['item:read'])]
    private ?string $imageLargeThumbnail = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['item:read'])]
    private int $seenCounter = 0;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['item:read', 'item:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    #[Groups(['item:read'])]
    private ?string $parentVisibility = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['item:read'])]
    private string $finalVisibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['item:read'])]
    private ?string $scrapedFromUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['item:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['item:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    private ?string $orderingValue = null;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->tags = new ArrayCollection();
        $this->data = new ArrayCollection();
        $this->relatedItems = new ArrayCollection();
        $this->relatedTo = new ArrayCollection();
        $this->loans = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getName() ?? '';
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

    public function getDataImages(): ArrayCollection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->in('type', DatumTypeEnum::IMAGE_TYPES))->orderBy(['position' => Order::Ascending]);

        return $this->data->matching($criteria);
    }

    public function getDataTexts(): DoctrineCollection
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->in('type', DatumTypeEnum::TEXT_TYPES))->orderBy(['position' => Order::Ascending]);

        return $this->data->matching($criteria);
    }

    public function hasTag(Tag $tag): bool
    {
        return $this->tags->contains($tag);
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

    public function setName(string $name): Item
    {
        $this->name = $name;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): Item
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSeenCounter(): ?int
    {
        return $this->seenCounter;
    }

    public function setSeenCounter(int $seenCounter): Item
    {
        $this->seenCounter = $seenCounter;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): Item
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): Item
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(?Collection $collection): Item
    {
        $this->collection = $collection;
        $this->setParentVisibility($collection->getFinalVisibility());

        return $this;
    }

    #[\Override]
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): Item
    {
        $this->owner = $owner;

        return $this;
    }

    public function setTags(DoctrineCollection $tags): Item
    {
        $this->tags = $tags;

        return $this;
    }

    public function getTags(): DoctrineCollection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): Item
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): Item
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function setRelatedItems(DoctrineCollection $relatedItems): Item
    {
        $this->relatedItems = $relatedItems;

        return $this;
    }

    public function getRelatedItems(): DoctrineCollection
    {
        return $this->relatedItems;
    }

    public function getAllRelatedItems(): DoctrineCollection
    {
        return new ArrayCollection(array_merge($this->relatedItems->toArray(), $this->relatedTo->toArray()));
    }

    public function addRelatedItem(Item $relatedItem): Item
    {
        if (!$this->relatedItems->contains($relatedItem)) {
            $this->relatedItems[] = $relatedItem;
        }

        return $this;
    }

    public function removeRelatedItem(Item $relatedItem): Item
    {
        if ($this->relatedItems->contains($relatedItem)) {
            $this->relatedItems->removeElement($relatedItem);
        }

        return $this;
    }

    public function getData(): DoctrineCollection
    {
        return $this->data;
    }

    public function addData(Datum $data): Item
    {
        if (!$this->data->contains($data)) {
            $this->data[] = $data;
            $data->setItem($this);
        }

        return $this;
    }

    public function removeData(Datum $data): Item
    {
        if ($this->data->contains($data)) {
            $this->data->removeElement($data);
        }

        return $this;
    }

    public function getLoans(): DoctrineCollection
    {
        return $this->loans;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): Item
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

    public function setImage(?string $image): Item
    {
        $this->image = $image;

        return $this;
    }

    public function getImageSmallThumbnail(): ?string
    {
        if ($this->imageSmallThumbnail) {
            return $this->imageSmallThumbnail;
        }

        if ($this->imageLargeThumbnail) {
            return $this->imageLargeThumbnail;
        }

        return $this->image;
    }

    public function setImageSmallThumbnail(?string $imageSmallThumbnail): Item
    {
        $this->imageSmallThumbnail = $imageSmallThumbnail;

        return $this;
    }

    public function getImageLargeThumbnail(): ?string
    {
        if (null === $this->imageLargeThumbnail) {
            return $this->image;
        }

        return $this->imageLargeThumbnail;
    }

    public function setImageLargeThumbnail(?string $imageLargeThumbnail): Item
    {
        $this->imageLargeThumbnail = $imageLargeThumbnail;

        return $this;
    }

    public function getOrderingValue(): ?string
    {
        return $this->orderingValue;
    }

    public function setOrderingValue(?string $orderingValue): Item
    {
        $this->orderingValue = $orderingValue;

        return $this;
    }

    public function getScrapedFromUrl(): ?string
    {
        return $this->scrapedFromUrl;
    }

    public function setScrapedFromUrl(?string $scrapedFromUrl): Item
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

        return $this;
    }
}
