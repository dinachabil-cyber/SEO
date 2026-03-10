<?php

namespace App\Entity;

use App\Repository\PageSectionRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageSectionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PageSection
{
    public const ALLOWED_TYPES = [
        'header',
        'hero',
        'hero_split',
        'body',
        'image',
        'cards',
        'faq',
        'form',
        'cta',
        'footer',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Page $page = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\Choice(choices: self::ALLOWED_TYPES, message: 'Invalid section type')]
    private ?string $type = null;

    #[ORM\Column]
    private ?int $position = 0;

    #[ORM\Column(type: 'json')]
    private array $data = [];

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'reference_section_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?ReferenceSection $referenceSection = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
        $this->data = [];
        $this->position = 0;
        $this->name = '';
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): static
    {
        $this->page = $page;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getReferenceSection(): ?ReferenceSection
    {
        return $this->referenceSection;
    }

    public function setReferenceSection(?ReferenceSection $referenceSection): static
    {
        $this->referenceSection = $referenceSection;
        return $this;
    }

    public function getEffectiveData(): array
    {
        $data = $this->referenceSection ? $this->referenceSection->getData() : $this->data;

        if (!isset($data['style']) || !is_array($data['style'])) {
            $data['style'] = [];
        }

        $defaultStyle = [
            'sticky' => false,
            'variant' => 'light',
            'background' => '',
            'textColor' => '',
            'backgroundVariant' => 'surface',
            'layout' => 'left',
            'columns' => 3,
            'cardVariant' => 'solid',
            'accentColor' => 'primary',
            'buttonColor' => 'primary',
            'buttonStyle' => 'primary',
            'borderColor' => '',
            'shadow' => false,
            'rounded' => false,
            'maxWidth' => 'normal',
            'textAlign' => 'left',
            'align' => 'center',
            'accordionVariant' => 'default',
        ];

        $data['style'] = array_merge($defaultStyle, $data['style']);

        return $data;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }
}