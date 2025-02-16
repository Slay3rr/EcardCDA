<?php
// Déclaration du namespace. Cela permet de définir à quel "espace" ou "dossier" cette classe appartient
// Ici, la classe UserController appartient à l'espace "App\Controller"
namespace App\Controller;

use App\Entity\User; // On importe la classe User. Cela permet de manipuler les entités User
use App\Repository\UserRepository; // On importe le repository User pour accéder aux données de la table 'user' dans la base de données
use Doctrine\ORM\EntityManagerInterface; // On importe EntityManagerInterface qui est utilisé pour interagir avec la base de données
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Importation de la classe de base de Symfony qui fournit des méthodes utiles pour les contrôleurs
use Symfony\Component\HttpFoundation\Request; // Importation de la classe Request qui gère les données envoyées dans la requête HTTP
use Symfony\Component\HttpFoundation\Response; // Importation de la classe Response qui est utilisée pour envoyer une réponse HTTP
use Symfony\Component\Routing\Annotation\Route; // On importe l'annotation Route, qui permet de définir des routes pour ce contrôleur
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\JsonResponse;



class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile', methods: ['GET'])]
    public function profile(): Response
    {
        // Affiche la page de profil de l'utilisateur connecté
        return $this->render('user/profile.html.twig');
    }

    #[Route('/profile/update', name: 'user_profile_update', methods: ['POST'])]
    public function updateProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer les données du formulaire
        $name = trim($request->request->get('name'));
        $firstName = trim($request->request->get('first_name'));
        $addressStreet = trim($request->request->get('address_street'));
        $addressCity = trim($request->request->get('address_city'));
        $addressPostal = trim($request->request->get('address_postal'));
        $addressCountry = trim($request->request->get('address_country'));
        $email = trim($request->request->get('email'));
        $plainPassword = $request->request->get('password');
        $passwordConfirmation = $request->request->get('password_confirmation');

        // Mise à jour des informations
        $user->setName($name);
        $user->setFirstName($firstName);
        $user->setAddressStreet($addressStreet);
        $user->setAddressCity($addressCity);
        $user->setAddressPostal($addressPostal);
        $user->setAddressCountry($addressCountry);
        $user->setEmail($email);

        // Modification du mot de passe si renseigné
        if (!empty($plainPassword)) {
            if ($plainPassword !== $passwordConfirmation) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('user_profile');
            }
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }

        $entityManager->flush();
        $this->addFlash('success', 'Votre profil a été mis à jour.');
        return $this->redirectToRoute('user_profile');
    }
    #[Route('/api/admin/users', name: 'admin_users_')]

    
        #[Route('', name: 'list', methods: ['GET'])]
        public function list(UserRepository $userRepository): JsonResponse
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            $users = $userRepository->findAll();
            return $this->json($users, 200, [], ['groups' => 'user:read']);
        }
    
        #[Route('', name: 'create', methods: ['POST'])]
        public function create(Request $request, EntityManagerInterface $em): JsonResponse
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            $data = json_decode($request->getContent(), true);
    
            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword(password_hash($data['password'], PASSWORD_BCRYPT));
            $user->setRoles(['ROLE_USER']);
    
            $em->persist($user);
            $em->flush();
    
            return $this->json(['message' => 'Utilisateur créé'], 201);
        }
    
        #[Route('/{id}', name: 'update', methods: ['PUT'])]
        public function update(int $id, Request $request, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            $user = $userRepository->find($id);
            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé'], 404);
            }
    
            $data = json_decode($request->getContent(), true);
            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }
            if (isset($data['roles'])) {
                $user->setRoles($data['roles']);
            }
    
            $em->flush();
            return $this->json(['message' => 'Utilisateur mis à jour'], 200);
        }
    
        #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
        public function delete(int $id, UserRepository $userRepository, EntityManagerInterface $em): JsonResponse
        {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
            $user = $userRepository->find($id);
            if (!$user) {
                return $this->json(['message' => 'Utilisateur non trouvé'], 404);
            }
    
            $em->remove($user);
            $em->flush();
            return $this->json(['message' => 'Utilisateur supprimé'], 200);
        }
        
    }
    

