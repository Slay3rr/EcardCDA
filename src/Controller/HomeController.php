<?php
// src/Controller/HomeController.php
namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        // Récupérer tous les articles triés par ID décroissant (on suppose que l'ID décroissant correspond aux nouveautés)
        $articles = $articleRepository->findBy([], ['id' => 'DESC']);

        // Simuler les différentes sections (à ajuster selon vos critères réels)
        $bestSellers = array_slice($articles, 0, 3);
        $nouveautes  = array_slice($articles, 3, 5);
        $tendances   = array_slice($articles, 8, 3);

        return $this->render('home/home_page.html.twig', [
            'bestSellers' => $bestSellers,
            'nouveautes'  => $nouveautes,
            'tendances'   => $tendances,
            'categories'  => $categoryRepository->findAll(),
        ]);
    }
}
