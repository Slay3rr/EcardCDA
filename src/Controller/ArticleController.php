<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface; // On importe EntityManagerInterface qui est utilisé pour interagir avec la base de données
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



class ArticleController extends AbstractController
{
    // Liste des articles publics (accessible à tous)
    #[Route('/articles', name: 'public_articles', methods: ['GET'])]
    public function publicIndex(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy([], ['id' => 'DESC']);
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    // Affichage d'un article spécifique (accessible à tous)
    #[Route('/article/{id}', name: 'public_article_show')]
    public function publicShow(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Route pour la création d'un nouvel article en mode admin
    #[Route('/admin/article/new', name: 'admin_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $article = new Article();
        $form = $this->createFormBuilder($article)
            ->add('Titre', TextType::class)
            ->add('content', TextareaType::class)
            ->add('Category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('admin_article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour l'affichage des articles admins
    #[Route('/admin/article', name: 'admin_article_index', methods: ['GET'])]
    public function adminIndex(ArticleRepository $articleRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findAll(),
        ]);
    }

    // Route pour l'affichage détaillé d'un article en mode admin
    #[Route('/admin/article/{id}', name: 'admin_article_show', methods: ['GET'])]
    public function adminShow(Article $article): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Route pour l'édition d'un article en mode admin
    #[Route('/admin/article/{id}/edit', name: 'admin_article_edit', methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $form = $this->createFormBuilder($article)
            ->add('Titre', TextType::class)
            ->add('content', TextareaType::class)
            ->add('Category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('admin_article_index');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    // Route pour supprimer un article en mode admin
    #[Route('/admin/article/{id}/delete', name: 'admin_article_delete', methods: ['POST'])]
    public function delete(Article $article, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('admin_article_index');
    }
}