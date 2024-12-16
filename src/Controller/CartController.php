<?php

namespace App\Controller;

use App\Entity\Article;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart_show')]
    public function showCart(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []); // Récupérer le panier depuis la session
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['article']->getPrice() * $item['quantity'];
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function addToCart(Article $article, Request $request, SessionInterface $session): Response
    {
        $quantity = $request->query->getInt('quantity', 1);
        $cart = $session->get('cart', []);

        if (isset($cart[$article->getId()])) {
            $cart[$article->getId()]['quantity'] += $quantity;
        } else {
            $cart[$article->getId()] = [
                'article' => $article,
                'quantity' => $quantity,
            ];
        }

        $session->set('cart', $cart); // Mettre à jour le panier dans la session

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function removeFromCart(Article $article, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$article->getId()])) {
            unset($cart[$article->getId()]);
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/cart/update/{id}', name: 'cart_update')]
    public function updateCart(Article $article, Request $request, SessionInterface $session): Response
    {
        $quantity = $request->query->getInt('quantity', 1);
        $cart = $session->get('cart', []);

        if (isset($cart[$article->getId()]) && $quantity > 0) {
            $cart[$article->getId()]['quantity'] = $quantity;
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_show');
    }
}