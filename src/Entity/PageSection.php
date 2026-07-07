<?php

namespace App\Entity;

use App\Repository\PageSectionRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: PageSectionRepository::class)]
#[ORM\HasLifecycleCallbacks]
class PageSection
{
    use SectionDataTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Page $page = null;

    #[ORM\Column]
    private ?int $position = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'reference_section_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?ReferenceSection $referenceSection = null;

    public function __construct()
    {
        $this->data = [];
        $this->position = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
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
        if ($this->referenceSection) {
            return $this->referenceSection->getData();
        }

        return $this->data;
    }
}
