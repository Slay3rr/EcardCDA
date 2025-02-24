<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['article:read', 'admin:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['article:read', 'admin:read'])]
    private ?string $Titre = null;

    #[ORM\Column(length: 255)]
    #[Groups(['article:read', 'admin:read'])]
    private ?string $content = null;
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['article:read', 'admin:read'])]
    private ?string $imageId = null;


    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'articles')]
    #[Groups(groups: ['article:read', 'admin:read'])]
    private Collection $Category;

    #[ORM\Column]
    #[Groups(groups: ['article:read', 'admin:read'])]
    private ?float $price = null;

    /**
     * @var Collection<int, Offre>
     */
    #[ORM\OneToMany(targetEntity: Offre::class, mappedBy: 'article',cascade: ['remove'], orphanRemoval: true)]
    #[Groups(groups: ['article:read', 'admin:read'])]
    private Collection $offres;

    public function __construct()
    {
        $this->Category = new ArrayCollection();
        $this->offres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    

    public function getTitre(): ?string
    {
        return $this->Titre;
    }

    public function setTitre(string $Titre): static
    {
        $this->Titre = $Titre;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategory(): Collection
    {
        return $this->Category;
    }
    
    // Getter et Setter pour 'price'
    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }



    public function addCategory(Category $category): static
    {
        if (!$this->Category->contains($category)) {
            $this->Category->add($category);
            $category->addArticle($this);
        }
    
        return $this;
    }
    
    public function removeCategory(Category $category): static
    {
        if ($this->Category->removeElement($category)) {
            $category->removeArticle($this);
        }
    
        return $this;
    }

    /**
     * @return Collection<int, Offre>
     */
    public function getOffres(): Collection
    {
        return $this->offres;
    }

    public function addOffre(Offre $offre): static
    {
        if (!$this->offres->contains($offre)) {
            $this->offres->add($offre);
            $offre->setArticle($this);
        }

        return $this;
    }

    public function removeOffre(Offre $offre): static
    {
        if ($this->offres->removeElement($offre)) {
            // set the owning side to null (unless already changed)
            if ($offre->getArticle() === $this) {
                $offre->setArticle(null);
            }
        }

        return $this;
    }
    
    public function getImageId(): ?string
    {
        return $this->imageId;
    }

    public function setImageId(?string $imageId): self
    {
        $this->imageId = $imageId;
        return $this;
    }
}
