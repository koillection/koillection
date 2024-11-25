<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Entity\Interfaces\LoggableInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'koi_inventory')]
#[ApiResource(
    operations: [
        new Get(),
        new Delete(),
        new GetCollection(),
    ],
    denormalizationContext: ['groups' => ['inventory:write']],
    normalizationContext: ['groups' => ['inventory:read']]
)]
class Inventory implements BreadcrumbableInterface, LoggableInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['inventory:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['inventory:read', 'inventory:write'])]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['inventory:read', 'inventory:write'])]
    #[Assert\NotBlank]
    private ?array $content = [];

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'inventories')]
    #[Groups(['inventory:read'])]
    private ?User $owner = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['inventory:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['inventory:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    public function getCheckedItemsCount(): int
    {
        $checkedItems = 0;
        foreach ($this->content as $rootCollection) {
            $checkedItems += $rootCollection['totalCheckedItems'];
        }

        return $checkedItems;
    }

    public function getTotalItemsCount(): int
    {
        $totalItems = 0;
        foreach ($this->content as $rootCollection) {
            $totalItems += $rootCollection['totalItems'];
        }

        return $totalItems;
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

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

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
}
