<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Repository\ScraperRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScraperRepository::class)]
#[ORM\Table(name: 'koi_scraper')]
class Scraper implements BreadcrumbableInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    private string $id;

    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 15)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $urlPattern = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $namePath = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $pricePath = null;

    #[ORM\OneToMany(targetEntity: Path::class, mappedBy: 'scraper', cascade: ['all'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => Order::Ascending->value])]
    #[Assert\Valid]
    #[AppAssert\UniqueDatumLabel]
    private DoctrineCollection $dataPaths;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'scrapers')]
    private ?User $owner = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
        $this->dataPaths = new ArrayCollection();
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

    public function setName(?string $name): Scraper
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): Scraper
    {
        $this->type = $type;

        return $this;
    }

    public function getNamePath(): ?string
    {
        return $this->namePath;
    }

    public function setNamePath(?string $namePath): Scraper
    {
        $this->namePath = $namePath;

        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): Scraper
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getPricePath(): ?string
    {
        return $this->pricePath;
    }

    public function setPricePath(?string $pricePath): Scraper
    {
        $this->pricePath = $pricePath;

        return $this;
    }

    public function getDataPaths(): DoctrineCollection
    {
        return $this->dataPaths;
    }

    public function addDataPath(Path $path): self
    {
        if (!$this->dataPaths->contains($path)) {
            $this->dataPaths[] = $path;
            $path->setScraper($this);
        }

        return $this;
    }

    public function removeDataPath(Path $path): self
    {
        if ($this->dataPaths->contains($path)) {
            $this->dataPaths->removeElement($path);
            // set the owning side to null (unless already changed)
            if ($path->getScraper() === $this) {
                $path->setScraper(null);
            }
        }

        return $this;
    }

    #[\Override]
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): Scraper
    {
        $this->owner = $owner;

        return $this;
    }

    public function getUrlPattern(): ?string
    {
        return $this->urlPattern;
    }

    public function setUrlPattern(?string $urlPattern): Scraper
    {
        $this->urlPattern = $urlPattern;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): Scraper
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): Scraper
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
