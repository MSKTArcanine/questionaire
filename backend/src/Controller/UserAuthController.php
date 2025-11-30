<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserAuthController extends AbstractController
{
    #[Route('/api/auth/user', name: 'api_user_login', methods: ['POST'])]
    public function userLogin(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];
        $email = $data['email'] ?? null;
        $pin   = $data['password'] ?? null; // on garde "password" pour rester cohérent

        if (!$email || !$pin) {
            return $this->json(['error' => 'Email et PIN requis'], 400);
        }

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            // Add user si inexistant
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);

            $hashed = $passwordHasher->hashPassword($user, $pin);
            $user->setPassword($hashed);

            $em->persist($user);
            $em->flush();
        } else {
            // PIN ?
            if (!$passwordHasher->isPasswordValid($user, $pin)) {
                return $this->json(['error' => 'PIN invalide'], 401);
            }
        }

        //JWT
        $token = $jwtManager->create($user);

        return $this->json(['token' => $token]);
    }
}
