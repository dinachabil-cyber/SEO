<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $domain = null;

    #[ORM\Column(length: 5, options: ['default' => 'fr'])]
    private ?string $defaultLocale = 'fr';

    #[ORM\Column(options: ['default' => true])]
    private ?bool $isActive = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hosting = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $databaseName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $databasePassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $technology = null;

    #[ORM\Column(nullable: true)]
    private ?DateTime $publishedAt = null;

    #[ORM\Column]
    private ?DateTime $createdAt = null;

    #[ORM\Column]
    private ?DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    private ?PasswordHasherFactoryInterface $passwordHasherFactory = null;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'site', orphanRemoval: true)]
    private Collection $pages;

    #[ORM\Column(options: ['default' => 0])]
    private int $pageCount = 0;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->defaultLocale = 'fr';
        $this->isActive = true;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): static
    {
        $this->domain = $domain;
        return $this;
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    public function setDefaultLocale(string $defaultLocale): static
    {
        $this->defaultLocale = $defaultLocale;
        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getHosting(): ?string
    {
        return $this->hosting;
    }

    public function setHosting(?string $hosting): static
    {
        $this->hosting = $hosting;
        return $this;
    }

    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    public function setDatabaseName(?string $databaseName): static
    {
        $this->databaseName = $databaseName;
        return $this;
    }

    public function getDatabasePassword(): ?string
    {
        return $this->databasePassword;
    }

    public function setDatabasePassword(?string $databasePassword): static
    {
        if ($databasePassword) {
            $this->databasePassword = $this->encodePassword($databasePassword);
        }
        return $this;
    }

    public function verifyDatabasePassword(string $password): bool
    {
        return password_verify($password, $this->databasePassword);
    }

    private function encodePassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getTechnology(): ?string
    {
        return $this->technology;
    }

    public function setTechnology(?string $technology): static
    {
        $this->technology = $technology;
        return $this;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt): static
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): static
    {
        if (!$this->pages->contains($page)) {
            $this->pages->add($page);
            $page->setSite($this);
        }

        return $this;
    }

    public function removePage(Page $page): static
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getSite() === $this) {
                $page->setSite(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Draft' => 'bg-secondary',
            'In Progress' => 'bg-primary',
            'Published' => 'bg-success',
            'Suspended' => 'bg-danger',
            'Archived' => 'bg-dark',
            default => 'bg-secondary',
        };
    }

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $companyName = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legalRepresentative = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $registrationNumber = null;

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): static
    {
        $this->companyName = $companyName;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getLegalRepresentative(): ?string
    {
        return $this->legalRepresentative;
    }

    public function setLegalRepresentative(?string $legalRepresentative): static
    {
        $this->legalRepresentative = $legalRepresentative;
        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(?string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;
        return $this;
    }

    public function hasImprintData(): bool
    {
        return $this->companyName || $this->address || $this->phone || $this->email || $this->legalRepresentative || $this->registrationNumber;
    }

    public function getPageCount(): int
    {
        return $this->pages->count();
    }
}
