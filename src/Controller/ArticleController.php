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
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\CardImage;

class ArticleController extends AbstractController
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    private function getImageUrls(array $articles): array
    {
        $imageUrls = [];
        foreach ($articles as $article) {
            if ($article->getImageId()) {
                $cardImage = $this->documentManager
                    ->getRepository(CardImage::class)
                    ->find($article->getImageId());
                
                if ($cardImage) {
                    $imageUrls[$article->getId()] = $cardImage->getUrl();
                }
            }
        }
        return $imageUrls;
    }

    #[Route('/articles', name: 'public_articles', methods: ['GET'])]
    public function publicIndex(ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $articles = $articleRepository->findBy([], ['id' => 'DESC']);
        
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'imageUrls' => $this->getImageUrls($articles)
        ]);
    }

    #[Route('/article/{id}', name: 'public_article_show')]
    public function publicShow(Article $article): Response
    {
        $imageUrls = $this->getImageUrls([$article]);
        $imageUrl = $imageUrls[$article->getId()] ?? null;

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'imageUrl' => $imageUrl
        ]);
    }

    #[Route('/articles/category', name: 'articles_by_category_search', methods: ['GET'])]
    public function articlesByCategorySearch(Request $request, CategoryRepository $categoryRepository): Response
    {
        $categoryId = $request->query->get('id');
        $category = $categoryId ? $categoryRepository->find($categoryId) : null;
        
        if ($categoryId && !$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        $articles = $category ? $category->getArticles()->toArray() : $categoryRepository->findAll();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'imageUrls' => $this->getImageUrls($articles)
        ]);
    }

    #[Route('/articles/category/{id}', name: 'articles_by_category', methods: ['GET'])]
    public function articlesByCategory(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException('La catégorie demandée n\'existe pas.');
        }

        $articles = $category->getArticles()->toArray();

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'currentCategory' => $category,
            'imageUrls' => $this->getImageUrls($articles)
        ]);
    }

    #[Route('/articles/search', name: 'article_search', methods: ['GET'])]
    public function search(Request $request, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $searchType = $request->query->get('search_type');
        $query = $request->query->get('query');
        $articles = [];

        if ($searchType === 'category' && $query) {
            $category = $categoryRepository->findByName($query);
            $articles = $category ? $articleRepository->findByCategory($category) : [];
        } elseif ($searchType === 'article' && $query) {
            $articles = $articleRepository->findByTitleOrContent($query);
        } else {
            $articles = $articleRepository->findAll();
        }

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'imageUrls' => $this->getImageUrls($articles)
        ]);
    }
}