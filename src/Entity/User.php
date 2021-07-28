<?php

declare(strict_types=1);

namespace App\Entity;

use App\Annotation\Upload;
use App\Entity\Interfaces\BreadcrumbableInterface;
use App\Enum\DateFormatEnum;
use App\Enum\LocaleEnum;
use App\Enum\RoleEnum;
use App\Enum\ThemeEnum;
use App\Enum\VisibilityEnum;
use DateTimeInterface;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="koi_user", indexes={
 *     @ORM\Index(name="idx_user_visibility", columns={"visibility"})
 * })
 * @UniqueEntity(fields={"email"}, message="error.email.not_unique")
 * @UniqueEntity(fields={"username"}, message="error.username.not_unique")
 */
class User implements UserInterface, BreadcrumbableInterface, \Serializable
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     */
    private UuidInterface $id;

    /**
     * @var ?string
     * @ORM\Column(type="string", length=32, unique=true)
     * @Assert\Regex(pattern="/^[a-z\d_]{2,32}$/i", message="error.username.incorrect")
     */
    private ?string $username = null;

    /**
     * @var ?string
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email()
     */
    private ?string $email = null;

    /**
     * @var ?string
     */
    private ?string $salt = null;

    /**
     * @var ?string
     * @ORM\Column(type="string", length=255)
     */
    private ?string $password;

    /**
     * @var ?string
     * @Assert\Regex(pattern="/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Za-z]).*$/", message="error.password.incorrect")
     */
    private ?string $plainPassword = null;

    /**
     * @var ?File
     * @Upload(path="avatar")
     */
    private ?File $file = null;

    /**
     * @var ?string
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    private ?string $avatar = null;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $enabled;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private array $roles;

    /**
     * @var string
     * @ORM\Column(type="string", length=3)
     */
    private string $currency;

    /**
     * @var string
     * @ORM\Column(type="string", length=5)
     */
    private string $locale;

    /**
     * @var ?string
     * @ORM\Column(type="string")
     */
    private ?string $timezone = null;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $dateFormat;

    /**
     * @var int
     * @ORM\Column(type="bigint", options={"default"=268435456})
     */
    private int $diskSpaceAllowed;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $visibility;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="Collection", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $collections;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $tags;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="TagCategory", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $tagCategories;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="Wishlist", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $wishlists;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="Template", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $templates;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="Log", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $logs;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="Album", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $albums;

    /**
     * @var DoctrineCollection
     * @ORM\OneToMany(targetEntity="Inventory", mappedBy="owner", cascade={"remove"})
     */
    private DoctrineCollection $inventories;

    /**
     * @var ?DateTimeInterface
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTimeInterface $lastDateOfActivity = null;

    /**
     * @var string
     * @ORM\Column(type="string", options={"default": "teal"})
     */
    private string $theme;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private bool $darkModeEnabled;

    /**
     * @var ?\DateTime
     * @ORM\Column(type="time", nullable=true)
     */
    private ?\DateTime $automaticDarkModeStartAt;

    /**
     * @var ?\DateTime
     * @ORM\Column(type="time", nullable=true)
     */
    private ?\DateTime $automaticDarkModeEndAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $wishlistsFeatureEnabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $tagsFeatureEnabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $signsFeatureEnabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $albumsFeatureEnabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $loansFeatureEnabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $templatesFeatureEnabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $historyFeatureEnabled;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default": 1})
     */
    private bool $statisticsFeatureEnabled;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $createdAt;

    /**
     * @var ?DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->roles = ['ROLE_USER'];
        $this->diskSpaceAllowed = 536870912;
        $this->enabled = true;
        $this->currency = 'EUR';
        $this->locale = LocaleEnum::LOCALE_EN_GB;
        $this->visibility = VisibilityEnum::VISIBILITY_PRIVATE;
        $this->dateFormat = DateFormatEnum::FORMAT_HYPHEN_YMD;
        $this->theme = ThemeEnum::THEME_TEAL;
        $this->darkModeEnabled = false;
        $this->wishlistsFeatureEnabled = true;
        $this->tagsFeatureEnabled = true;
        $this->signsFeatureEnabled = true;
        $this->albumsFeatureEnabled = true;
        $this->loansFeatureEnabled = true;
        $this->templatesFeatureEnabled = true;
        $this->historyFeatureEnabled = true;
        $this->statisticsFeatureEnabled = true;
    }

    public function serialize(): ?string
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUsername() ?? '';
    }

    public function isAdmin(): bool
    {
        return \in_array(RoleEnum::ROLE_ADMIN, $this->roles, true);
    }

    public function getCurrentTheme() : string
    {
        if ($this->isDarkModeEnabled()) {
            return 'dark';
        }

        return $this->theme;
    }

    public function isInDarkMode() : bool
    {
        if ($this->isDarkModeEnabled()) {
            return true;
        }

        if ($this->getAutomaticDarkModeStartAt() && $this->getAutomaticDarkModeEndAt()) {
            // Apply timezone to get current time for the user
            $timezone = new \DateTimeZone('Europe/Paris');
            $currentTime = strtotime((new \DateTime())->setTimezone($timezone)->format('H:i'));
            $startTime = strtotime($this->getAutomaticDarkModeStartAt()->format('H:i'));
            $endTime = strtotime($this->getAutomaticDarkModeEndAt()->format('H:i'));

            if (
                (
                    $startTime < $endTime &&
                    $currentTime >= $startTime &&
                    $currentTime <= $endTime
                ) ||
                (
                    $startTime > $endTime && (
                        $currentTime >= $startTime ||
                        $currentTime <= $endTime
                    )
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function getDateFormatForJs() : string
    {
        return DateFormatEnum::MAPPING[$this->dateFormat][DateFormatEnum::CONTEXT_JS];
    }

    public function getDateFormatForForm() : string
    {
        return DateFormatEnum::MAPPING[$this->dateFormat][DateFormatEnum::CONTEXT_FORM];
    }

    public function getOwner(): ?self
    {
        return $this;
    }

    public function eraseCredentials()
    {
    }

    public function getUsername() : ?string
    {
        return $this->username;
    }

    public function getSalt() : ?string
    {
        return $this->salt;
    }

    public function setSalt(?string $salt) : self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setPassword(string $password) : self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword() : ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword) : self
    {
        $this->plainPassword = $plainPassword;
        $this->password = $plainPassword;

        return $this;
    }

    public function getRoles() : array
    {
        return $this->roles;
    }

    public function setRoles(array $roles) : self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role) : self
    {
        $role = strtoupper($role);
        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role) : self
    {
        if (false !== $key = \array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = \array_values($this->roles);
        }

        return $this;
    }

    public function getId() : ?string
    {
        return $this->id->toString();
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
        return $this->diskSpaceAllowed;
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

    public function getLastDateOfActivity(): ?DateTimeInterface
    {
        return $this->lastDateOfActivity;
    }

    public function setLastDateOfActivity(?DateTimeInterface $lastDateOfActivity): self
    {
        $this->lastDateOfActivity = $lastDateOfActivity;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
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
        //Force Doctrine to trigger an update
        if ($file instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTime());
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

    public function getTheme(): string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): User
    {
        $this->theme = $theme;

        return $this;
    }

    public function isDarkModeEnabled(): bool
    {
        return $this->darkModeEnabled;
    }

    public function setDarkModeEnabled(bool $darkModeEnabled): User
    {
        $this->darkModeEnabled = $darkModeEnabled;

        return $this;
    }

    public function getAutomaticDarkModeStartAt(): ?\DateTime
    {
        return $this->automaticDarkModeStartAt;
    }

    public function setAutomaticDarkModeStartAt(?\DateTime $automaticDarkModeStartAt): User
    {
        $this->automaticDarkModeStartAt = $automaticDarkModeStartAt;

        return $this;
    }

    public function getAutomaticDarkModeEndAt(): ?\DateTime
    {
        return $this->automaticDarkModeEndAt;
    }

    public function setAutomaticDarkModeEndAt(?\DateTime $automaticDarkModeEndAt): User
    {
        $this->automaticDarkModeEndAt = $automaticDarkModeEndAt;

        return $this;
    }
}
