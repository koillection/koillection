<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Attribute\Upload;
use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Enum\DateFormatEnum;
use App\Enum\DisplayModeEnum;
use App\Enum\RoleEnum;
use App\Enum\ThemeEnum;
use App\Enum\VisibilityEnum;
use App\Repository\UserRepository;
use App\Validator as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'koi_user')]
#[ORM\Index(name: 'idx_user_visibility', columns: ['visibility'])]
#[UniqueEntity(fields: ['email'], message: 'error.email.not_unique')]
#[UniqueEntity(fields: ['username'], message: 'error.username.not_unique')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection()
    ],
    denormalizationContext: ['groups' => ['user:write']],
    normalizationContext: ['groups' => ['user:read']]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, BreadcrumbableInterface, \Stringable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['user:read'])]
    private string $id;

    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    #[Assert\Regex(pattern: '/^[a-z\\d_]{2,32}$/i', message: 'error.username.incorrect')]
    #[Groups(['user:read', 'user:write'])]
    private ?string $username = null;

    #[ORM\Column(type: Types::STRING, unique: true)]
    #[Assert\Email]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    #[Assert\Regex(pattern: "/(?=^.{8,}\$)((?=.*\\d)|(?=.*\\W+))(?![.\n])(?=.*[A-Za-z]).*\$/", message: 'error.password.incorrect')]
    #[Groups(['user:read', 'user:write'])]
    private ?string $plainPassword = null;

    #[Upload(pathProperty: 'avatar', deleteProperty: 'deleteAvatar', maxWidth: 200, maxHeight: 200)]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['user:write', 'user:image'])]
    private ?File $file = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['user:read'])]
    private ?string $avatar = null;

    #[Groups(['collection:write'])]
    private ?bool $deleteAvatar = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $enabled = true;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: Types::STRING, length: 3)]
    #[Assert\Currency]
    #[Groups(['user:read', 'user:write'])]
    private string $currency = 'EUR';

    #[ORM\Column(type: Types::STRING, length: 5)]
    #[Groups(['user:read', 'user:write'])]
    #[AppAssert\AvailableLocale]
    private string $locale = 'en';

    #[ORM\Column(type: Types::STRING, length: 50)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Timezone]
    private ?string $timezone = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Choice(choices: DateFormatEnum::FORMATS)]
    private string $dateFormat = DateFormatEnum::FORMAT_HYPHEN_YMD;

    #[ORM\Column(type: Types::BIGINT, options: ['default' => 536870912])]
    #[Groups(['user:read'])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'integer',
        ],
        jsonSchemaContext: [
            'type' => 'integer',
        ]
    )]
    private int $diskSpaceAllowed = 536870912;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['user:read', 'user:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PRIVATE;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToOne(targetEntity: DisplayConfiguration::class, cascade: ['all'], orphanRemoval: true)]
    private ?DisplayConfiguration $collectionsDisplayConfiguration = null;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToOne(targetEntity: DisplayConfiguration::class, cascade: ['all'], orphanRemoval: true)]
    private ?DisplayConfiguration $wishlistsDisplayConfiguration = null;

    #[ApiProperty(readableLink: false, writableLink: false)]
    #[ORM\OneToOne(targetEntity: DisplayConfiguration::class, cascade: ['all'], orphanRemoval: true)]
    private ?DisplayConfiguration $albumsDisplayConfiguration = null;

    #[ORM\OneToMany(targetEntity: Collection::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $collections;

    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $tags;

    #[ORM\OneToMany(targetEntity: TagCategory::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $tagCategories;

    #[ORM\OneToMany(targetEntity: Wishlist::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $wishlists;

    #[ORM\OneToMany(targetEntity: Template::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $templates;

    #[ORM\OneToMany(targetEntity: Log::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $logs;

    #[ORM\OneToMany(targetEntity: Album::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $albums;

    #[ORM\OneToMany(targetEntity: Inventory::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $inventories;

    #[ORM\OneToMany(targetEntity: Scraper::class, mappedBy: 'owner', cascade: ['remove'])]
    private DoctrineCollection $scrapers;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $lastDateOfActivity = null;

    #[ORM\Column(type: Types::STRING, options: ['default' => ThemeEnum::THEME_BROWSER])]
    #[Assert\Choice(choices: ThemeEnum::THEMES)]
    private string $theme = ThemeEnum::THEME_BROWSER;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $wishlistsFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $tagsFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $signsFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $albumsFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $loansFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $templatesFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $historyFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $statisticsFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 1])]
    #[Groups(['user:read', 'user:write'])]
    private bool $scrapingFeatureEnabled = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Groups(['user:read', 'user:write'])]
    private bool $searchInDataByDefaultEnabled = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    #[Groups(['user:read', 'user:write'])]
    private bool $displayItemsNameInGridView = false;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['user:read', 'user:write'])]
    private string $searchResultsDisplayMode = DisplayModeEnum::DISPLAY_MODE_GRID;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['user:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->scrapers = new ArrayCollection();
        $this->collections = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->tagCategories = new ArrayCollection();
        $this->wishlists = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->albums = new ArrayCollection();
        $this->inventories = new ArrayCollection();
        $this->collectionsDisplayConfiguration = (new DisplayConfiguration())->setOwner($this);
        $this->albumsDisplayConfiguration = (new DisplayConfiguration())->setOwner($this);
        $this->wishlistsDisplayConfiguration = (new DisplayConfiguration())->setOwner($this);
        $this->id = Uuid::v7()->toRfc4122();
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getUsername();
    }

    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    public function __serialize()
    {
        return [$this->id, $this->username, $this->password];
    }

    public function __unserialize(array $data): void
    {
        [$this->id, $this->username, $this->password] = $data;
    }

    public function isAdmin(): bool
    {
        return \in_array(RoleEnum::ROLE_ADMIN, $this->roles, true);
    }

    public function getDateFormatWithTime(): string
    {
        return $this->dateFormat . ' H:i:s';
    }

    public function getDateFormatForJs(): string
    {
        return DateFormatEnum::MAPPING[$this->dateFormat][DateFormatEnum::CONTEXT_JS];
    }

    public function getDateFormatForForm(): string
    {
        return DateFormatEnum::MAPPING[$this->dateFormat][DateFormatEnum::CONTEXT_FORM];
    }

    public function getOwner(): ?self
    {
        return $this;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setSalt(?string $salt): self
    {
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        $this->password = $plainPassword;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);
        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        if (false !== ($key = array_search(strtoupper($role), $this->roles, true))) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getDiskSpaceAllowed(): ?int
    {
        return (int) $this->diskSpaceAllowed;
    }

    public function setDiskSpaceAllowed(int $diskSpaceAllowed): self
    {
        $this->diskSpaceAllowed = $diskSpaceAllowed;

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

    public function getLastDateOfActivity(): ?\DateTimeImmutable
    {
        return $this->lastDateOfActivity;
    }

    public function setLastDateOfActivity(?\DateTimeImmutable $lastDateOfActivity): self
    {
        $this->lastDateOfActivity = $lastDateOfActivity;

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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

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

    public function isWishlistsFeatureEnabled(): bool
    {
        return $this->wishlistsFeatureEnabled;
    }

    public function setWishlistsFeatureEnabled(bool $wishlistsFeatureEnabled): User
    {
        $this->wishlistsFeatureEnabled = $wishlistsFeatureEnabled;

        return $this;
    }

    public function isTagsFeatureEnabled(): bool
    {
        return $this->tagsFeatureEnabled;
    }

    public function setTagsFeatureEnabled(bool $tagsFeatureEnabled): User
    {
        $this->tagsFeatureEnabled = $tagsFeatureEnabled;

        return $this;
    }

    public function isSignsFeatureEnabled(): bool
    {
        return $this->signsFeatureEnabled;
    }

    public function setSignsFeatureEnabled(bool $signsFeatureEnabled): User
    {
        $this->signsFeatureEnabled = $signsFeatureEnabled;

        return $this;
    }

    public function isAlbumsFeatureEnabled(): bool
    {
        return $this->albumsFeatureEnabled;
    }

    public function setAlbumsFeatureEnabled(bool $albumsFeatureEnabled): User
    {
        $this->albumsFeatureEnabled = $albumsFeatureEnabled;

        return $this;
    }

    public function isLoansFeatureEnabled(): bool
    {
        return $this->loansFeatureEnabled;
    }

    public function setLoansFeatureEnabled(bool $loansFeatureEnabled): User
    {
        $this->loansFeatureEnabled = $loansFeatureEnabled;

        return $this;
    }

    public function isTemplatesFeatureEnabled(): bool
    {
        return $this->templatesFeatureEnabled;
    }

    public function setTemplatesFeatureEnabled(bool $templatesFeatureEnabled): User
    {
        $this->templatesFeatureEnabled = $templatesFeatureEnabled;

        return $this;
    }

    public function isHistoryFeatureEnabled(): bool
    {
        return $this->historyFeatureEnabled;
    }

    public function setHistoryFeatureEnabled(bool $historyFeatureEnabled): User
    {
        $this->historyFeatureEnabled = $historyFeatureEnabled;

        return $this;
    }

    public function isStatisticsFeatureEnabled(): bool
    {
        return $this->statisticsFeatureEnabled;
    }

    public function setStatisticsFeatureEnabled(bool $statisticsFeatureEnabled): User
    {
        $this->statisticsFeatureEnabled = $statisticsFeatureEnabled;

        return $this;
    }

    public function isScrapingFeatureEnabled(): bool
    {
        return $this->scrapingFeatureEnabled;
    }

    public function setScrapingFeatureEnabled(bool $scrapingFeatureEnabled): User
    {
        $this->scrapingFeatureEnabled = $scrapingFeatureEnabled;

        return $this;
    }

    public function getCollectionsDisplayConfiguration(): ?DisplayConfiguration
    {
        return $this->collectionsDisplayConfiguration;
    }

    public function setCollectionsDisplayConfiguration(?DisplayConfiguration $collectionsDisplayConfiguration): User
    {
        $this->collectionsDisplayConfiguration = $collectionsDisplayConfiguration;

        return $this;
    }

    public function getWishlistsDisplayConfiguration(): ?DisplayConfiguration
    {
        return $this->wishlistsDisplayConfiguration;
    }

    public function setWishlistsDisplayConfiguration(?DisplayConfiguration $wishlistsDisplayConfiguration): User
    {
        $this->wishlistsDisplayConfiguration = $wishlistsDisplayConfiguration;

        return $this;
    }

    public function getAlbumsDisplayConfiguration(): ?DisplayConfiguration
    {
        return $this->albumsDisplayConfiguration;
    }

    public function setAlbumsDisplayConfiguration(?DisplayConfiguration $albumsDisplayConfiguration): User
    {
        $this->albumsDisplayConfiguration = $albumsDisplayConfiguration;

        return $this;
    }

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): User
    {
        $this->theme = $theme;

        return $this;
    }

    public function isSearchInDataByDefaultEnabled(): bool
    {
        return $this->searchInDataByDefaultEnabled;
    }

    public function setSearchInDataByDefaultEnabled(bool $searchInDataByDefaultEnabled): User
    {
        $this->searchInDataByDefaultEnabled = $searchInDataByDefaultEnabled;

        return $this;
    }

    public function isDisplayItemsNameInGridView(): bool
    {
        return $this->displayItemsNameInGridView;
    }

    public function setDisplayItemsNameInGridView(bool $displayItemsNameInGridView): User
    {
        $this->displayItemsNameInGridView = $displayItemsNameInGridView;

        return $this;
    }

    public function getDeleteAvatar(): ?bool
    {
        return $this->deleteAvatar;
    }

    public function setDeleteAvatar(?bool $deleteAvatar): User
    {
        $this->deleteAvatar = $deleteAvatar;
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getSearchResultsDisplayMode(): string
    {
        return $this->searchResultsDisplayMode;
    }

    public function setSearchResultsDisplayMode(string $searchResultsDisplayMode): User
    {
        $this->searchResultsDisplayMode = $searchResultsDisplayMode;

        return $this;
    }
}
