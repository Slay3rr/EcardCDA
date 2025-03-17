<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\CardImage;
use App\Form\SearchType;


class HomeController extends AbstractController
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    private function getImagesForArticles($articles): array
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

    #[Route('/', name: 'home')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Création du formulaire de recherche
        $form = $this->createForm(SearchType::class, null, [
            'action' => $this->generateUrl('article_search'),
            'method' => 'GET'
        ]);
        // Récupération des articles
        $bestSellers = $articleRepository->findBy([], ['id' => 'DESC'], 3);
        $nouveautes = $articleRepository->findBy([], ['id' => 'DESC'], 4);
        $tendances = $articleRepository->findBy([], ['id' => 'DESC'], 3);

        // Récupération des images pour chaque section
        $bestSellersImages = $this->getImagesForArticles($bestSellers);
        $nouveautesImages = $this->getImagesForArticles($nouveautes);
        $tendancesImages = $this->getImagesForArticles($tendances);

        return $this->render('home/home_page.html.twig', [
            'searchForm' => $form->createView(),
            'bestSellers' => $bestSellers,
            'nouveautes' => $nouveautes,
            'tendances' => $tendances,
            'bestSellersImages' => $bestSellersImages,
            'nouveautesImages' => $nouveautesImages,
            'tendancesImages' => $tendancesImages,
        ]);
    }
}