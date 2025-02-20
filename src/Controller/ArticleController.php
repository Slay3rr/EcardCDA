<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;




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