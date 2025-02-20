<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;


class SecurityController extends AbstractController
{

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/api/login', name: 'api_login', methods: ['POST'])]
public function adminLogin(
    Request $request, 
    UserRepository $userRepository, 
    UserPasswordHasherInterface $passwordHasher,
    JWTTokenManagerInterface $jwtManager
): JsonResponse {
    $data = json_decode($request->getContent(), true);
    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    $user = $userRepository->findOneBy(['email' => $email]);

    if (!$user) {
        return $this->json(['message' => 'User not found'], Response::HTTP_UNAUTHORIZED);
    }

    if (!$passwordHasher->isPasswordValid($user, $password)) {
        return $this->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
    }

    if (!in_array('ROLE_ADMIN', $user->getRoles())) {
        return $this->json(['message' => 'Access denied. Admin role required.'], Response::HTTP_FORBIDDEN);
    }

    $token = $jwtManager->create($user);
    return $this->json([
        'token' => $token,
        'user' => [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ]
    ]);
}
    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    } 
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $name = trim($request->request->get('name'));
            $firstName = trim($request->request->get('first_name'));
            $username = trim($request->request->get('username'));
            $addressStreet = trim($request->request->get('address_street'));
            $addressCity = trim($request->request->get('address_city'));
            $addressPostal = trim($request->request->get('address_postal'));
            $addressCountry = trim($request->request->get('address_country'));
            $email = trim($request->request->get('email'));
            $plainPassword = $request->request->get('password');
            $passwordConfirmation = $request->request->get('password_confirmation');
            $termsAccepted = $request->request->get('terms'); // "on" si coché

            // Vérification de la complétude des champs obligatoires
            if (empty($name) || empty($firstName) || empty($username) ||
                empty($addressStreet) || empty($addressCity) || empty($addressPostal) || empty($addressCountry) ||
                empty($email) || empty($plainPassword)) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
                return $this->redirectToRoute('app_register');
            }

            // Vérifier que les mots de passe correspondent
            if ($plainPassword !== $passwordConfirmation) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_register');
            }

            // Valider la complexité du mot de passe (8-64 caractères, lettres, chiffres, caractère spécial)
            if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,64}$/', $plainPassword)) {
                $this->addFlash('error', 'Le mot de passe doit comporter entre 8 et 64 caractères et contenir au moins une lettre, un chiffre et un caractère spécial.');
                return $this->redirectToRoute('app_register');
            }

            // Vérifier l'acceptation des conditions
            if (!$termsAccepted) {
                $this->addFlash('error', 'Vous devez accepter les conditions d\'utilisation.');
                return $this->redirectToRoute('app_register');
            }

            // Création du nouvel utilisateur
            $user = new User();
            $user->setName($name);
            $user->setFirstName($firstName);
            $user->setUsername($username);
            $user->setAddressStreet($addressStreet);
            $user->setAddressCity($addressCity);
            $user->setAddressPostal($addressPostal);
            $user->setAddressCountry($addressCountry);
            $user->setEmail($email);

            // Hasher le mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);

            // Vous pouvez également définir d'autres valeurs par défaut (ex: rôle ROLE_USER)
            $user->setRoles(['ROLE_USER']);

            // Enregistrer l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été créé avec succès ! Connectez-vous.');
            return $this->redirectToRoute('app_login');
        }

        // Afficher le formulaire d'inscription
        return $this->render('user/register.html.twig');
    }

    
}