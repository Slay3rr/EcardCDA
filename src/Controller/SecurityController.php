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
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;

class SecurityController extends AbstractController
{

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $form = $this->createForm(LoginFormType::class);
        
        return $this->render('security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
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
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que les mots de passe correspondent
            $plainPassword = $form->get('plainPassword')->getData();
            $passwordConfirmation = $form->get('passwordConfirmation')->getData();
    
            if ($plainPassword !== $passwordConfirmation) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_register');
            }
    
            // Hash du mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_USER']);
    
            $entityManager->persist($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre compte a été créé avec succès ! Connectez-vous.');
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('user/register.html.twig', [
            'registrationForm' => $form->createView()
        ]);
    }
}