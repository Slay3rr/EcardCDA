<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;



#[ORM\Entity]
#[UniqueEntity(fields: ["email"], message: "Cet email est déjà utilisé.")]
#[UniqueEntity(fields: ["username"], message: "Ce nom d'utilisateur est déjà pris.")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["user:read", "admin:read"])]

    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(["user:read", "admin:read"])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(["user:read", "admin:read"])]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;


    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(["user:read", "admin:read"])]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(["user:read", "admin:read"])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true, unique: true)]
    #[Groups(["user:read", "admin:read"])]
    private ?string $username = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $addressStreet = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $addressCity = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $addressPostal = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $addressCountry = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Offre::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $offres;

    public function __construct()
    {
        $this->offres = new ArrayCollection();
    }
    

   
    public function getId(): ?int
    {
        return $this->id;
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

    public function getRoles(): array
    {
        $roles = $this->roles;
        // Garantir que chaque user a ROLE_USER par défaut
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
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

    // Méthode requise par PasswordAuthenticatedUserInterface
    public function eraseCredentials(): void
    {
        // Rien de particulier ici, sauf si vous stockez des données temporaires sensibles
    }

    // Méthode requise par UserInterface, identifiant principal
    public function getUserIdentifier(): string
    {
        return $this->email ?? '';
    }

    // GET/SET name
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    // GET/SET firstName
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    // GET/SET username
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    // GET/SET addressStreet
    public function getAddressStreet(): ?string
    {
        return $this->addressStreet;
    }

    public function setAddressStreet(?string $addressStreet): self
    {
        $this->addressStreet = $addressStreet;
        return $this;
    }

    // GET/SET addressCity
    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): self
    {
        $this->addressCity = $addressCity;
        return $this;
    }

    // GET/SET addressPostal
    public function getAddressPostal(): ?string
    {
        return $this->addressPostal;
    }

    public function setAddressPostal(?string $addressPostal): self
    {
        $this->addressPostal = $addressPostal;
        return $this;
    }

    // GET/SET addressCountry
    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry): self
    {
        $this->addressCountry = $addressCountry;
        return $this;
    }
    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): self
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setUser($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): self
    {
        if ($this->offres->removeElement($offre)) {
            if ($offre->getUser() === $this) {
                $offre->setUser(null);
            }
        }

        return $this;
    }
}

