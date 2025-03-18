<?php

namespace App\Controller;

use App\Form\SearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/conditions-utilisation', name: 'app_terms')]
    public function terms(): Response
    {
        $form = $this->createForm(SearchType::class, null, [
            'action' => $this->generateUrl('article_search'),
            'method' => 'GET'
        ]);

        return $this->render('legal/terms.html.twig', [
            'searchForm' => $form->createView()
        ]);
    }
}