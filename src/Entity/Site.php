<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use App\Entity\Event;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['domain'], message: 'This domain already exists.')]
class Site
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private string $domain;

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
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $owner = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    private ?PasswordHasherFactoryInterface $passwordHasherFactory = null;

    /**
     * @var Collection<int, Page>
     */
    #[ORM\OneToMany(targetEntity: Page::class, mappedBy: 'site', orphanRemoval: true)]
    private Collection $pages;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'site', orphanRemoval: true)]
    private Collection $events;

    #[ORM\Column(options: ['default' => 0])]
    private int $pageCount = 0;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
        $this->events = new ArrayCollection();
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
        if (empty($this->databasePassword)) {
            return null;
        }
        
        // Decrypt the password using OpenSSL
        $key = $this->getEncryptionKey();
        $data = base64_decode($this->databasePassword);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encryptedPassword = substr($data, $ivLength);
        
        $decrypted = openssl_decrypt($encryptedPassword, 'aes-256-cbc', $key, 0, $iv);
        
        return $decrypted === false ? null : $decrypted;
    }

    public function setDatabasePassword(?string $databasePassword): static
    {
        if ($databasePassword) {
            $this->databasePassword = $this->encryptPassword($databasePassword);
        } else {
            $this->databasePassword = null;
        }
        return $this;
    }

    private function encryptPassword(string $password): string
    {
        $key = $this->getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($password, 'aes-256-cbc', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    private function getEncryptionKey(): string
    {
        // Get encryption key from environment variables
        // Try multiple ways to get the environment variable
        $key = $_ENV['DATABASE_PASSWORD_ENCRYPTION_KEY'] ?? 
               $_SERVER['DATABASE_PASSWORD_ENCRYPTION_KEY'] ?? 
               getenv('DATABASE_PASSWORD_ENCRYPTION_KEY') ?? '';
        
        // Fallback to a default key if none is found (for development purposes only)
        if (empty($key) || $key === 'change_this_to_a_secure_random_string_at_least_32_characters_long') {
            $key = 'default_development_encryption_key_32_chars!';
        }
        
        // Ensure key is 32 bytes for AES-256
        return hash('sha256', $key, true);
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

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
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
        return $this->pageCount;
    }

    public function setPageCount(int $pageCount): static
    {
        $this->pageCount = $pageCount;
        return $this;
    }

    public function incrementPageCount(): static
    {
        $this->pageCount++;
        return $this;
    }

    public function decrementPageCount(): static
    {
        $this->pageCount--;
        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setSite($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getSite() === $this) {
                $event->setSite(null);
            }
        }

        return $this;
    }
}
