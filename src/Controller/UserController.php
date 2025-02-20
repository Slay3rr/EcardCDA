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
    
        
    }
    

