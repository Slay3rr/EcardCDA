<?php

namespace App\Controller;

use App\Entity\Offre; // Nouvelle entité Offre
use App\Entity\CartItem; // L'entité qui gère le panier
use App\Repository\OffreRepository; // Le repository pour Offre
use App\Repository\CartItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class CartController extends AbstractController
{
    // Afficher le panier
    #[Route('/cart', name: 'cart_show')]
    public function showCart(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        // dump($cart); die ;
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['offer']->getPrice() * $item['quantity'];
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }



    // Ajouter une offre au panier


    #[Route('/cart/add/{id}', name: 'cart_add')]
public function addToCart(Offre $offre, Request $request, SessionInterface $session, EntityManagerInterface $em): Response
{
    if (!$offre->getId()) {
        throw $this->createNotFoundException('L\'offre spécifiée est invalide.');
    }
    
    // Récupérer la quantité demandée depuis la requête, par défaut 1
    $quantity = $request->query->getInt('quantity', 1);

    // Vérification : S'assurer que la quantité demandée est disponible
    if ($offre->getQuantity() < $quantity) {
        $this->addFlash('error', 'La quantité demandée dépasse celle disponible.');
        return $this->redirectToRoute('cart_show');
    }

    // Récupérer le panier depuis la session
    $cart = $session->get('cart', []);

    // Ajouter l'offre au panier
    if (isset($cart[$offre->getId()])) {
        $cart[$offre->getId()]['quantity'] += $quantity;
    } else {
        $cart[$offre->getId()] = [
            'offer' => $offre,
            'quantity' => $quantity,
        ];
    }

    // Mettre à jour la quantité disponible de l'offre
    $offre->setQuantity($offre->getQuantity() - $quantity);

    // Sauvegarder les changements dans la base de données
    $em->persist($offre);
    $em->flush();

    // Mettre à jour le panier dans la session
    $session->set('cart', $cart);

    $this->addFlash('success', 'Article ajouté au panier.');
    return $this->redirectToRoute('cart_show');


}
#[Route('/cart/clear', name: 'cart_clear')]
public function clearCart(SessionInterface $session): Response
{
    $session->remove('cart'); // Supprime la clé 'cart' de la session
    $this->addFlash('success', 'Votre panier a été vidé.');
    return $this->redirectToRoute('cart_show');
}



    // Supprimer une offre du panier
    #[Route('/cart/remove/{id}', name: 'cart_remove')]
public function removeFromCart(Offre $offre, SessionInterface $session, EntityManagerInterface $em): Response
{
    $cart = $session->get('cart', []);

    // Vérifie si l'offre existe dans le panier
    if (isset($cart[$offre->getId()])) {
        // Récupère la quantité supprimée et la restitue à l'offre
        $removedQuantity = $cart[$offre->getId()]['quantity'];
        $offre->setQuantity($offre->getQuantity() + $removedQuantity);

        // Supprime l'article du panier
        unset($cart[$offre->getId()]);

        // Met à jour la base de données
        $em->persist($offre);
        $em->flush();
    }

    // Met à jour la session
    $session->set('cart', $cart);

    $this->addFlash('success', 'L\'article a été supprimé du panier.');
    return $this->redirectToRoute('cart_show');
}

#[Route('/cart/update/{id}', name: 'cart_update', methods: ['POST'])]
public function updateCart(Offre $offre, Request $request, SessionInterface $session, EntityManagerInterface $em): Response
{
    $cart = $session->get('cart', []);
    $newQuantity = $request->request->getInt('quantity'); // Nouvelle quantité envoyée par le formulaire

    // Vérification : La quantité ne peut pas être négative
    if ($newQuantity < 0) {
        $this->addFlash('error', 'La quantité ne peut pas être négative.');
        return $this->redirectToRoute('cart_show');
    }

    if (isset($cart[$offre->getId()])) {
        $currentQuantity = $cart[$offre->getId()]['quantity'];
        $difference = $newQuantity - $currentQuantity;

        // Si la quantité est 0, retirer l'offre du panier
        if ($newQuantity === 0) {
            unset($cart[$offre->getId()]);
            $offre->setQuantity($offre->getQuantity() + $currentQuantity); // Remettre la quantité dans l'offre
            $this->addFlash('success', 'L\'offre a été retirée du panier.');
        } else {
            // Vérification de la quantité disponible dans l'offre
            if ($offre->getQuantity() < $difference) {
                $this->addFlash('error', 'La quantité demandée dépasse celle disponible.');
                return $this->redirectToRoute('cart_show');
            }

            // Mettre à jour les quantités
            $cart[$offre->getId()]['quantity'] = $newQuantity;
            $offre->setQuantity($offre->getQuantity() - $difference);
        }

        // Mettre à jour la session et persister les changements
        $session->set('cart', $cart);
        $em->persist($offre);
        $em->flush();

        $this->addFlash('success', 'La quantité a été mise à jour.');
    }

    return $this->redirectToRoute('cart_show');
}


}