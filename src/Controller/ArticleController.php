<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface; // On importe EntityManagerInterface qui est utilisé pour interagir avec la base de données
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;



class ArticleController extends AbstractController
{
    // Liste des articles publics (accessible à tous)
    #[Route('/articles', name: 'public_articles', methods: ['GET'])]
    public function publicIndex(ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $articles = $articleRepository->findBy([], ['id' => 'DESC']);
        $categories = $categoryRepository->findAll(); // Récupération des catégories

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories, // Transmet les catégories à la vue
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
    #[Route('/api/article/new', name: 'api_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $article = new Article();
        $form = $this->createFormBuilder($article)
        ->add('Titre', TextType::class)
        ->add('content', TextareaType::class)
        ->add('price', NumberType::class, [
            'label' => 'Prix',
            'scale' => 2, // Pour avoir un prix avec 2 décimales
            'attr' => ['step' => '0.01', 'min' => '0'],
        ])
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
            return $this->redirectToRoute('api_article_index');
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Route pour l'affichage des articles admins
    #[Route('/api/article', name: 'api_article_index', methods: ['GET'])]
    public function adminIndex(ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $articles = $articleRepository->findAll();
        $categories = $categoryRepository->findAll(); // Récupération des catégories

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories, // Transmet les catégories à la vue
        ]);
    }

    // Route pour l'affichage détaillé d'un article en mode admin
    #[Route('/api/article/{id}', name: 'api_article_show', methods: ['GET'])]
    public function adminShow(Article $article): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        return $this->render('article/show.html.twig', [
            'article' => $article,
        ]);
    }

    // Route pour l'édition d'un article en mode admin
    #[Route('/api/article/{id}/edit', name: 'api_article_edit', methods: ['GET', 'POST'])]
    public function edit(Article $article, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $form = $this->createFormBuilder($article)
        ->add('Titre', TextType::class)
        ->add('content', TextareaType::class)
        ->add('price', NumberType::class, [
            'label' => 'Prix',
            'scale' => 2, // Pour avoir un prix avec 2 décimales
            'attr' => ['step' => '0.01', 'min' => '0'],
        ])
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
            return $this->redirectToRoute('api_article_index');
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    // Route pour supprimer un article en mode admin
    #[Route('/api/article/{id}/delete', name: 'api_article_delete', methods: ['POST'])]
    public function delete(Article $article, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('api_article_index');
    }

    #[Route('/articles/category', name: 'articles_by_category_search', methods: ['GET'])]
public function articlesByCategorySearch(Request $request, CategoryRepository $categoryRepository, ArticleRepository $articleRepository): Response
{
    $categoryId = $request->query->get('id'); // Récupère l'ID depuis le formulaire.

    if ($categoryId) {
        $category = $categoryRepository->find($categoryId);
        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        $articles = $category->getArticles(); // Récupérer les articles liés à cette catégorie.
    } else {
        // Si aucune catégorie n'est sélectionnée, afficher tous les articles.
        $articles = $articleRepository->findAll();
    }

    return $this->render('article/index.html.twig', [
        'articles' => $articles,
        'categories' => $categoryRepository->findAll(), // Pour le menu de catégorie
    ]);
}


#[Route('/articles/category/{id}', name: 'articles_by_category', methods: ['GET'])]
public function articlesByCategory(int $id, CategoryRepository $categoryRepository, ArticleRepository $articleRepository): Response
{
    $category = $categoryRepository->find($id);
    if (!$category) {
        throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
    }
    $articles = $category->getArticles();

    return $this->render('article/index.html.twig', [
        'articles' => $articles,
        'categories' => $categoryRepository->findAll(),
        'currentCategory' => $category,
    ]);
}

#[Route('/articles/search', name: 'article_search', methods: ['GET'])]
public function search(Request $request, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
{
    $searchType = $request->query->get('search_type');
    $query = $request->query->get('query');

    if ($searchType === 'category') {
        // Utiliser une recherche partielle sur le nom de catégorie
        $category = $categoryRepository->findByName($query);
        if ($category) {
            $articles = $articleRepository->findByCategory($category);
        } else {
            $articles = [];
        }
    } elseif ($searchType === 'article') {
        $articles = $articleRepository->findByTitleOrContent($query);
    } else {
        $articles = $articleRepository->findAll();
    }

    return $this->render('article/index.html.twig', [
        'articles' => $articles,
        'categories' => $categoryRepository->findAll(),
    ]);
}


}