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
use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Entity\Interfaces\LoggableInterface;
use App\Repository\TagCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TagCategoryRepository::class)]
#[ORM\Table(name: 'koi_tag_category')]
#[ApiResource(
    denormalizationContext: ['groups' => ['tagCategory:write']],
    normalizationContext: ['groups' => ['tagCategory:read']],
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
    ],
)]
#[ApiResource(uriTemplate: '/tags/{id}/category', uriVariables: ['id' => new Link(fromClass: Tag::class, fromProperty: 'category')], normalizationContext: ['groups' => ['tagCategory:read']], operations: [new Get()])]
class TagCategory implements BreadcrumbableInterface, LoggableInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['tagCategory:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['tagCategory:read', 'tagCategory:write'])]
    #[Assert\NotBlank]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['tagCategory:read', 'tagCategory:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    #[Groups(['tagCategory:read', 'tagCategory:write'])]
    private ?string $color = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tagCategories')]
    #[Groups(['tagCategory:read'])]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'category')]
    private DoctrineCollection $tags;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['tagCategory:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['tagCategory:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->tags = new ArrayCollection();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

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

    public function getTags(): DoctrineCollection
    {
        return $this->tags;
    }
}
