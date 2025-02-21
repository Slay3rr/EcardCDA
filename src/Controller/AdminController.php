<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Offre;
use App\Entity\Category;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use App\Repository\OffreRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Form\ArticleType;

#[Route('/api/admin')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArticleRepository $articleRepository,
        private CategoryRepository $categoryRepository,
        private OffreRepository $offreRepository,
        private UserRepository $userRepository,
        private SerializerInterface $serializer,

    ) {}
    #[Route('/articles/categories', name: 'api_admin_categories_list', methods: ['GET'])]
    public function getCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        try {
            $categories = $categoryRepository->findAll();
            
            $categoriesArray = array_map(function($category) {
                return [
                    'id' => $category->getId(),
                    'name' => $category->getName()
                ];
            }, $categories);
            
            return $this->json($categoriesArray);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération des catégories',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    // ==================== USERS ====================
    #[Route('/users', name: 'admin_users_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $users = $this->userRepository->findAll();
        
        return $this->json($users, 200, [], ['groups' => ['admin:read']]);
    }

    #[Route('/users/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $user = $this->userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return $this->json($user, 200, [], ['groups' => ['admin:read']]);
    }



    #[Route('/users/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], 404);
        }
    
        $em->remove($user);
        $em->flush();
        return $this->json(['message' => 'Utilisateur supprimé avec succès'], 200);
    }
    

   // ==================== ARTICLES ====================

    #[Route('/articles', name: 'admin_articles_list', methods: ['GET'])]
    public function listArticles(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $articles = $this->articleRepository->findAll();
        
        return $this->json($articles, 200, [], ['groups' => ['admin:read']]);
    }

    #[Route('/articles/{id}', name: 'admin_article_show', methods: ['GET'])]
    public function showArticle(int $id): JsonResponse
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');
    
    $article = $this->articleRepository->find($id);
    if (!$article) {
        return $this->json(['message' => 'Article non trouvé'], 404);
    }

    return $this->json($article, 200, [], ['groups' => ['admin:read']]);
}
#[Route('/articles', name: 'admin_article_create', methods: ['POST'])]
public function createArticle(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    try {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $data = json_decode($request->getContent(), true);
        
        // Transformer Category en tableau simple
        if (isset($data['Category'])) {
            $data['Category'] = [$data['Category']];
        }
        
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->submit($data);

        // Vérification des erreurs de formulaire
        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $errors[] = [
                    'field' => $error->getOrigin() ? $error->getOrigin()->getName() : 'global',
                    'message' => $error->getMessage()
                ];
            }
            return $this->json(['errors' => $errors], 400);
        }

        // Sauvegarde en base de données
        $entityManager->persist($article);
        $entityManager->flush();

        // Préparation des catégories pour la réponse
        $categories = [];
        foreach ($article->getCategory() as $cat) {
            $categories[] = [
                'id' => $cat->getId(),
                'name' => $cat->getName()
            ];
        }

        // Réponse de succès
        return $this->json([
            'message' => 'Article créé avec succès',
            'article' => [
                'id' => $article->getId(),
                'titre' => $article->getTitre(),
                'content' => $article->getContent(),
                'price' => $article->getPrice(),
                'categories' => $categories
            ]
        ], 201);
        
    } catch (\Exception $e) {
        return $this->json([
            'error' => 'Erreur serveur',
            'message' => $e->getMessage(),
            'data' => $data ?? null
        ], 500);
    }
}    #[Route('/articles/{id}', name: 'admin_article_update', methods: ['PUT'])]
    public function updateArticle(int $id, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $article = $this->articleRepository->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }

        $form = $this->createForm(ArticleType::class, $article);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isValid()) {
            $this->entityManager->flush();
            return $this->json($article, 200, [], ['groups' => ['admin:read']]);
        }

        // Récupération des erreurs de validation
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = [
                'field' => $error->getOrigin()->getName(),
                'message' => $error->getMessage()
            ];
        }

        return $this->json(['errors' => $errors], 400);
    }

    #[Route('/articles/{id}', name: 'admin_article_delete', methods: ['DELETE'])]
    public function deleteArticle(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $article = $this->articleRepository->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }

        $this->entityManager->remove($article);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }

    // ==================== OFFRES ====================

    #[Route('/articles/{id}/offres', name: 'admin_article_offres', methods: ['GET'])]
    public function getArticleOffres(int $id): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $article = $this->articleRepository->find($id);
        if (!$article) {
            return $this->json(['message' => 'Article non trouvé'], 404);
        }

        return $this->json($article->getOffres(), 200, [], [
            'groups' => ['admin:read'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
    }
    #[Route('/offre/{id}', name: 'admin_offer_delete', methods: ['DELETE'])]
    public function adminDelete(Offre $offre): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->entityManager->remove($offre);
        $this->entityManager->flush();

        $this->addFlash('success', 'L\'offre a été supprimée avec succès.');
        return $this->redirectToRoute('public_articles');
    }

    
}