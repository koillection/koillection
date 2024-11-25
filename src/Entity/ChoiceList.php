<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Entity\Interfaces\LoggableInterface;
use App\Repository\ChoiceListRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChoiceListRepository::class)]
#[ORM\Table(name: 'koi_choice_list')]
#[ApiResource(
    denormalizationContext: ['groups' => ['choiceList:write']],
    normalizationContext: ['groups' => ['choiceList:read']],
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
    ],
)]
class ChoiceList implements BreadcrumbableInterface, LoggableInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['choiceList:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups(['choiceList:read', 'choiceList:write'])]
    private string $name;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['choiceList:read', 'choiceList:write'])]
    #[Assert\Unique]
    private array $choices = [];

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['choiceList:read'])]
    private ?User $owner = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['choiceList:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['choiceList:read'])]
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

    #[\Override]
    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ChoiceList
    {
        $this->name = $name;

        return $this;
    }

    public function getChoices(): array
    {
        return $this->choices;
    }

    public function setChoices(array $choices): ChoiceList
    {
        $this->choices = $choices;

        return $this;
    }

    #[\Override]
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): ChoiceList
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): ChoiceList
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): ChoiceList
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
