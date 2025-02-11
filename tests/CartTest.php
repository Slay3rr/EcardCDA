<?php
namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Cart;
use App\Entity\CartItem;

class CartTest extends TestCase
{
    public function testAddCart(): void
    {
        // Création d'une instance de Cart
        $cart = new Cart();

        // Création d'un objet CartItem
        $cartItem = new CartItem();

        // Ajout de l'item dans le panier
        $cart->addCartItem($cartItem);

        // Vérifie que l'élément existe dans la collection
        $this->assertContains($cartItem, $cart->getCartItems(), 'L\'élément doit exister dans la collection.');


        // Vérifie que le type de la collection est un ArrayCollection
        $this->assertInstanceOf(
            \Doctrine\Common\Collections\ArrayCollection::class,
            $cart->getCartItems(),        );

        // Ajout du même élément une deuxième fois
        $cart->addCartItem($cartItem);

        //  Vérifie que le nombre d'éléments n'a pas changé
        $this->assertCount(
            1,
            $cart->getCartItems(),
        );

    }
}
