<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\SearchType;
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
        $searchForm = $this->createForm(SearchType::class); // Ajout du formulaire

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'imageUrls' => $this->getImageUrls($articles),
            'searchForm' => $searchForm->createView() // Passage du formulaire à la vue

        ]);
    }

    #[Route('/article/{id}', name: 'public_article_show')]
    public function publicShow(Article $article): Response
    {
        $imageUrls = $this->getImageUrls([$article]);
        $imageUrl = $imageUrls[$article->getId()] ?? null;
        $searchForm = $this->createForm(SearchType::class); // Ajout du formulaire


        return $this->render('article/show.html.twig', [
            'article' => $article,
            'imageUrl' => $imageUrl,
            'searchForm' => $searchForm->createView() // Passage du formulaire à la vue

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
        $searchForm = $this->createForm(SearchType::class); // Ajout du formulaire


        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'imageUrls' => $this->getImageUrls($articles),
            'searchForm' => $searchForm->createView() // Passage du formulaire à la vue

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
        $searchForm = $this->createForm(SearchType::class); // Ajout du formulaire


        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'currentCategory' => $category,
            'imageUrls' => $this->getImageUrls($articles),
            'searchForm' => $searchForm->createView() // Passage du formulaire à la vue

        ]);
    }

    #[Route('/articles/search', name: 'article_search', methods: ['GET'])]
    public function search(Request $request, ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        
        $articles = [];
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $searchType = $data['search_type'];
            $query = $data['query'];
            
            if ($searchType === 'category') {
                $category = $categoryRepository->findByName($query);
                $articles = $category ? $articleRepository->findByCategory($category) : [];
            } elseif ($searchType === 'article') {
                $articles = $articleRepository->findByTitleOrContent($query);
            }
        }
    
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categories' => $categoryRepository->findAll(),
            'imageUrls' => $this->getImageUrls($articles),
            'searchForm' => $form->createView()
        ]);
    }
}