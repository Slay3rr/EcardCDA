<?php

namespace App\Entity;

use App\Repository\CartItemRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Offre;

#[ORM\Entity(repositoryClass: CartItemRepository::class)]
class CartItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Offre::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offre $offer = null; // Remplacer Article par Offer

    #[ORM\Column]
    private int $quantity = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOffer(): ?Offre
    {
        return $this->offer;
    }

    public function setOffer(Offre $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
    private ?Cart $cart = null;

public function getCart(): ?Cart
{
    return $this->cart;
}

public function setCart(?Cart $cart): self
{
    $this->cart = $cart;

    return $this;
}
}