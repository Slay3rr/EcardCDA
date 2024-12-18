<?php

namespace App\Controller;

use App\Entity\Offre;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OffreController extends AbstractController
{
    #[Route('/article/{id}/add-offer', name: 'add_offer', methods: ['GET', 'POST'])]
    public function addOffer(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER'); // Seuls les utilisateurs peuvent ajouter une offre

        $offre = new Offre();
        $offre->setArticle($article);
        $offre->setUser($this->getUser());

        $form = $this->createFormBuilder($offre)
            ->add('price', \Symfony\Component\Form\Extension\Core\Type\NumberType::class, [
                'label' => 'Prix de l\'offre',
                'required' => true
            ])
            ->add('quantity', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Quantité',
                'required' => true
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offre);
            $entityManager->flush();

            return $this->redirectToRoute('public_article_show', ['id' => $article->getId()]);
        }

        return $this->render('offre/add.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/offre/{id}/edit', name: 'offer_edit', methods: ['GET', 'POST'])]
    public function edit(Offre $offre, Request $request, EntityManagerInterface $em): Response
    {
        // Vérifier que l'utilisateur connecté est le propriétaire de l'offre
        if ($offre->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette offre.');
            return $this->redirectToRoute('public_articles');
        }

        // Créer un formulaire pour modifier le prix et la quantité
        $form = $this->createFormBuilder($offre)
            ->add('price', null, [
                'label' => 'Prix de l\'offre',
                'attr' => ['class' => 'form-control']
            ])
            ->add('quantity', null, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Votre offre a été mise à jour.');
            return $this->redirectToRoute('public_articles');
        }

        return $this->render('offer/edit.html.twig', [
            'form' => $form->createView(),
            'offre' => $offre,
        ]);
    }
}

