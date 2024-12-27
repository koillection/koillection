<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ErrorRepository;
use App\Repository\LogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ErrorRepository::class)]
#[ORM\Table(name: 'koi_error')]
class Error
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    private string $id;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(type: Types::SMALLINT)]
    private int $level;

    #[ORM\Column]
    private string $levelName;

    #[ORM\Column(type: Types::JSON)]
    private array $trace;

    #[ORM\Column(type: Types::INTEGER)]
    private int $count;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['item:read'])]
    private \DateTimeImmutable $lastOccurrenceAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['item:read'])]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->count = 0;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): Error
    {
        $this->message = $message;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): Error
    {
        $this->level = $level;

        return $this;
    }

    public function getLevelName(): string
    {
        return $this->levelName;
    }

    public function setLevelName(string $levelName): Error
    {
        $this->levelName = $levelName;

        return $this;
    }

    public function getTrace(): array
    {
        return $this->trace;
    }

    public function setTrace(array $trace): Error
    {
        $this->trace = $trace;

        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): Error
    {
        $this->count = $count;

        return $this;
    }

    public function getLastOccurrenceAt(): \DateTimeImmutable
    {
        return $this->lastOccurrenceAt;
    }

    public function setLastOccurrenceAt(\DateTimeImmutable $lastOccurrenceAt): Error
    {
        $this->lastOccurrenceAt = $lastOccurrenceAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): Error
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
