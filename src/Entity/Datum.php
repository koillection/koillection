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
use App\Entity\Interfaces\VisibleInterface;
use App\Entity\Traits\VisibleTrait;
use App\Enum\DatumTypeEnum;
use App\Enum\VisibilityEnum;
use App\Repository\DatumRepository;
use App\Validator as AppAssert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DatumRepository::class)]
#[ORM\Table(name: 'koi_datum')]
#[ORM\Index(name: 'idx_datum_label', columns: ['label'])]
#[ORM\Index(name: 'idx_datum_final_visibility', columns: ['final_visibility'])]
#[Assert\Expression('this.getItem() == null or this.getCollection() == null', message: 'error.datum.cant_be_used_by_both_collections_and_items')]
#[Assert\Expression('this.getItem() != null or this.getCollection() != null', message: 'error.datum.must_provide_collection_or_item')]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new Patch(),
        new GetCollection(),
        new Post(),
        new Post(uriTemplate: '/data/{id}/image', denormalizationContext: ['groups' => ['datum:image']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Datum image.')),
        new Post(uriTemplate: '/data/{id}/file', denormalizationContext: ['groups' => ['datum:file']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Datum file.')),
        new Post(uriTemplate: '/data/{id}/video', denormalizationContext: ['groups' => ['datum:video']], inputFormats: ['multipart' => ['multipart/form-data']], openapi: new OpenApiOperation(summary: 'Upload the Datum video.'))
    ],

    denormalizationContext: ['groups' => ['datum:write']],
    normalizationContext: ['groups' => ['datum:read']]
)]
#[ApiResource(uriTemplate: '/collections/{id}/data', uriVariables: ['id' => new Link(fromClass: Collection::class, fromProperty: 'data')], normalizationContext: ['groups' => ['datum:read']], operations: [new GetCollection()])]
#[ApiResource(uriTemplate: '/items/{id}/data', uriVariables: ['id' => new Link(fromClass: Item::class, fromProperty: 'data')], normalizationContext: ['groups' => ['datum:read']], operations: [new GetCollection()])]
class Datum implements VisibleInterface, \Stringable
{
    use VisibleTrait;

    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true, options: ['fixed' => true])]
    #[Groups(['datum:read'])]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Item::class, inversedBy: 'data', cascade: ["persist"])]
    #[Groups(['datum:read', 'datum:write'])]
    #[AppAssert\DatumLabelNotExistsInParent]
    private ?Item $item = null;

    #[ORM\ManyToOne(targetEntity: Collection::class, inversedBy: 'data')]
    #[Groups(['datum:read', 'datum:write'])]
    #[AppAssert\DatumLabelNotExistsInParent]
    private ?Collection $collection = null;

    #[ORM\Column(type: Types::STRING, length: 15)]
    #[Groups(['datum:read', 'datum:write'])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: DatumTypeEnum::TYPES)]
    #[ApiProperty(
        openapiContext: [
            'example' => 'text, textarea, country, date, rating, number, price, link, list, choice-list, checkbox, image, file, sign, video'
        ]
    )]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['datum:read', 'datum:write'])]
    #[Assert\NotBlank]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'example' => 'See wiki: https://github.com/benjaminjonard/koillection/wiki/API#example-values-for-data-fields'
        ]
    )]
    private ?string $label = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['datum:read', 'datum:write'])]
    #[Assert\When(expression: 'this.getType() == "rating"', constraints: [new Assert\Choice(choices: ['1','2','3','4','5','6','7','8','9','10'], message: 'error.datum.value.rating')])]
    #[Assert\When(expression: 'this.getType() == "checkbox"', constraints: [new Assert\Choice(choices: ['0', '1'], message: 'error.datum.value.checkbox')])]
    #[Assert\When(expression: 'this.getType() == "price"', constraints: [new Assert\Regex(pattern: '/^(?:\d+|\d*\.\d+)$/', message: 'error.datum.value.price')])]
    #[Assert\When(expression: 'this.getType() == "number"', constraints: [new Assert\Regex(pattern: '/^-?(?:\d+|\d*\.\d+)$/', message: 'error.datum.value.number')])]
    #[Assert\When(expression: 'this.getType() == "country"', constraints: [new Assert\Country(message: 'error.datum.value.country')])]
    #[Assert\When(expression: 'this.getType() == "date"', constraints: [new Assert\Date(message: 'error.datum.value.date')])]
    #[Assert\When(expression: 'this.getType() == "list"', constraints: [new Assert\Json])]
    #[Assert\When(expression: 'this.getType() == "link"', constraints: [new Assert\Url(requireTld: true)])]
    #[Assert\When(expression: 'this.getType() == "choice-list"', constraints: [new Assert\Json])]
    #[Assert\When(expression: 'this.getType() == "image"', constraints: [new Assert\IsNull])]
    #[Assert\When(expression: 'this.getType() == "sign"', constraints: [new Assert\IsNull])]
    #[Assert\When(expression: 'this.getType() == "file"', constraints: [new Assert\IsNull])]
    #[Assert\When(expression: 'this.getType() == "video"', constraints: [new Assert\IsNull])]
    private ?string $value = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    #[Groups(['datum:read', 'datum:write'])]
    private ?int $position = null;

    #[ORM\Column(type: Types::STRING, length: 3, nullable: true)]
    #[Assert\Currency]
    #[Groups(['datum:read', 'datum:write'])]
    private ?string $currency = null;

    #[Upload(pathProperty: 'image', smallThumbnailPathProperty: 'imageSmallThumbnail', largeThumbnailPathProperty: 'imageLargeThumbnail')]
    #[Assert\Image(mimeTypes: ['image/png', 'image/jpeg', 'image/webp', 'image/avif'], groups: ['datum:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['datum:write', 'datum:image'])]
    private ?File $fileImage = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['datum:read'])]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['datum:read'])]
    private ?string $imageSmallThumbnail = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['datum:read'])]
    private ?string $imageLargeThumbnail = null;

    #[Upload(pathProperty: 'file', originalFilenamePathProperty: 'originalFilename')]
    #[Assert\File(groups: ['datum:image'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['datum:write', 'datum:file'])]
    private ?File $fileFile = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['datum:read'])]
    private ?string $file = null;

    #[Upload(pathProperty: 'video')]
    #[Assert\File(mimeTypes: ['video/mp4', 'video/webm'], groups: ['datum:video'])]
    #[AppAssert\HasEnoughSpaceForUpload]
    #[Groups(['datum:write', 'datum:video'])]
    private ?File $fileVideo = null;

    #[ORM\Column(type: Types::STRING, nullable: true, unique: true)]
    #[Groups(['datum:read'])]
    private ?string $video = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['datum:read'])]
    private ?string $originalFilename = null;

    #[ORM\ManyToOne(targetEntity: ChoiceList::class)]
    #[Groups(['datum:read', 'datum:write'])]
    #[Assert\When(
        expression: 'this.getType() == "choice-list"',
        constraints: [
            new Assert\NotNull()
        ],
    )]
    private ?ChoiceList $choiceList = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['datum:read'])]
    private ?User $owner = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['datum:read', 'datum:write'])]
    #[Assert\Choice(choices: VisibilityEnum::VISIBILITIES)]
    private string $visibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::STRING, length: 10, nullable: true)]
    #[Groups(['datum:read'])]
    private ?string $parentVisibility = null;

    #[ORM\Column(type: Types::STRING, length: 10)]
    #[Groups(['datum:read'])]
    private string $finalVisibility = VisibilityEnum::VISIBILITY_PUBLIC;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['datum:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['datum:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v7()->toRfc4122();
    }

    public function __toString(): string
    {
        return $this->getLabel() ?? '';
    }

    public function getOrderedListChoices(): array
    {
        if ($this->value === null) {
            return [];
        }

        $selectedChoices = json_decode($this->value, true);
        $orderedSelectedChoices = [];

        foreach ($this->getChoiceList()->getChoices() as $availableChoice) {
            if (\in_array($availableChoice, $selectedChoices)) {
                $orderedSelectedChoices[] = $availableChoice;
            }
        }

        // If the selected choice wasn't found in the available choices, add it at the end of the list
        // It happens when the list was updated and the selected choice isn't available anymore
        foreach ($selectedChoices as $selectedChoice) {
            if (!\in_array($selectedChoice, $orderedSelectedChoices)) {
                $orderedSelectedChoices[] = $selectedChoice;
            }
        }

        return $orderedSelectedChoices;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

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

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;
        $this->setParentVisibility($item?->getFinalVisibility());

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

    public function getFileImage(): ?File
    {
        return $this->fileImage;
    }

    public function setFileImage(?File $fileImage): self
    {
        $this->fileImage = $fileImage;

        // Force Doctrine to trigger an update
        if ($fileImage instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

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

    public function setImageSmallThumbnail(?string $imageSmallThumbnail): self
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

    public function setImageLargeThumbnail(?string $imageLargeThumbnail): self
    {
        $this->imageLargeThumbnail = $imageLargeThumbnail;

        return $this;
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function setCollection(?Collection $collection): self
    {
        $this->collection = $collection;
        $this->setParentVisibility($collection?->getFinalVisibility());

        return $this;
    }

    public function getFileFile(): ?File
    {
        return $this->fileFile;
    }

    public function setFileFile(?File $fileFile): Datum
    {
        $this->fileFile = $fileFile;

        // Force Doctrine to trigger an update
        if ($fileFile instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): Datum
    {
        $this->file = $file;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): Datum
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getChoiceList(): ?ChoiceList
    {
        return $this->choiceList;
    }

    public function setChoiceList(?ChoiceList $choiceList): Datum
    {
        $this->choiceList = $choiceList;

        return $this;
    }

    public function getFileVideo(): ?File
    {
        return $this->fileVideo;
    }

    public function setFileVideo(?File $fileVideo): Datum
    {
        $this->fileVideo = $fileVideo;

        // Force Doctrine to trigger an update
        if ($fileVideo instanceof UploadedFile) {
            $this->setUpdatedAt(new \DateTimeImmutable());
        }

        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): Datum
    {
        $this->video = $video;

        return $this;
    }

    public function updateDescendantsVisibility(): self
    {
        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): Datum
    {
        $this->currency = $currency;

        return $this;
    }
}
