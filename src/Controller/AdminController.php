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
use App\Service\CloudinaryService;
use App\Document\CardImage;
use Doctrine\ODM\MongoDB\DocumentManager;


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
        private CloudinaryService $cloudinaryService


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
public function createArticle(Request $request): JsonResponse
{
    try {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        $data = json_decode($request->getContent(), true);
        
        // Créer l'article
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        
        // Transformer Category en tableau si nécessaire
        if (isset($data['Category']) && !is_array($data['Category'])) {
            $data['Category'] = [$data['Category']];
        }
        
        $form->submit($data);

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

        // Récupérer l'ID de l'image sélectionnée
        if (isset($data['imageId'])) {
            $article->setImageId($data['imageId']);
        }

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $this->json($article, 201, [], ['groups' => ['admin:read']]);
        
    } catch (\Exception $e) {
        return $this->json([
            'error' => 'Erreur lors de la création de l\'article',
            'message' => $e->getMessage()
        ], 500);
    }
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
    #[Route('/articles/{articleId}/offres/{id}', name: 'admin_offer_delete', methods: ['DELETE'])]
        public function deleteOffer(int $articleId, Offre $offre): JsonResponse
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');

            // Vérifier que l'offre appartient bien à l'article
            if ($offre->getArticle()->getId() !== $articleId) {
                return $this->json(['message' => 'Cette offre n\'appartient pas à cet article'], 404);
            }

            $this->entityManager->remove($offre);
            $this->entityManager->flush();

            return $this->json(null, 204);
        }
    
}