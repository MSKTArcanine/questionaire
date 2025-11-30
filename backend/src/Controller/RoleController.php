<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class RoleController extends AbstractController
{
    //En gros on rajoute un controller pour check les roles, on le laisse en ADMIN.
    #[Route('/api/auth/me', name: 'api_auth_me', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            // Pas de JWT / JWT invalide => 401
            return $this->json(['error' => 'Unauthorized'], 401);
        }

        return $this->json([
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
