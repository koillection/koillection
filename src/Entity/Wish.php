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
use App\Repository\WishRepository;
use App\Validator as AppAssert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WishRepository::class)]
#[ORM\Table(name: 'koi_wish')]
#[ORM\Index(name: 'idx_wish_final_visibility', columns: ['final_visibility'])]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
        new Post(uriTemplate: '/wishes/{id}/image', denormalizationContext: ['groups' => ['wish:image']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Wish image.')),
    ], denormalizationContext: ['groups' => ['wish:write']],
    normalizationContext: ['groups' => ['wish:read']]
)]
#[ApiResource(uriTemplate: '/wishlists/{id}/wishes', uriVariables: ['id' => new Link(fromClass: Wishlist::class, fromProperty: 'wishes')], normalizationContext: ['groups' => ['wish:read']], operations: [new GetCollection()])]
class Wish implements CacheableInterface, LoggableInterface, VisibleInterface, \Stringable
{
    use VisibleTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['wish:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['wish:read', 'wish:write'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['wish:read', 'wish:write'])]
    private ?string $url = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['wish:read', 'wish:write'])]
    private ?string $price = null;

    #[ORM\Column(type: Types::STRING, length: 6, nullable: true)]
    #[Groups(['wish:read', 'wish:write'])]
    #[Assert\Currency]
    private ?string $currency = null;

    #[ORM\ManyToOne(targetEntity: Wishlist::class, inversedBy: 'wishes')]
    #[Assert\NotBlank]
    #[Groups(['wish:read', 'wish:write'])]
    private ?Wishlist $wishlist = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['wish:read'])]
    private ?User $owner = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['wish:read', 'wish:write'])]
    private ?string $comment = null;

    #[Upload(pathProperty: 'image', smallThumbnailPathProperty: 'imageSmallThumbnail')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['wish:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['wish:write', 'wish:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['wish:read'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['wish:read'])]
    private ?string $imageSmallThumbnail = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['wish:read', 'wish:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    #[Groups(['wish:read'])]
    private ?string $parentVisibility = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['wish:read'])]
    private ?string $finalVisibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['wish:read'])]
    private ?string $scrapedFromUrl = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['wish:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['wish:read'])]
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
        return $this->getName() ?? '';
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

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

    public function getWishlist(): ?Wishlist
    {
        return $this->wishlist;
    }

    public function setWishlist(?Wishlist $wishlist): self
    {
        $this->wishlist = $wishlist;
        $this->setParentVisibility($wishlist->getFinalVisibility());

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

    public function getScrapedFromUrl(): ?string
    {
        return $this->scrapedFromUrl;
    }

    public function setScrapedFromUrl(?string $scrapedFromUrl): Wish
    {
        $this->scrapedFromUrl = $scrapedFromUrl;

        return $this;
    }

    #[\Override]
    public function updateDescendantsVisibility(): self
    {
        return $this;
    }
}
