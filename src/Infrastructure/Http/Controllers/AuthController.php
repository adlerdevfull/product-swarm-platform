<?php

declare(strict_types=1);

namespace Infrastructure\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AuthController extends AbstractController
{
    #[Route('/api/v1/auth/me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var \Infrastructure\Persistence\Models\UserModel $user */
        $user = $this->getUser();

        return new JsonResponse(['data' => [
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'name' => $user->getName(),
            'roles' => $user->getRoles(),
        ]]);
    }
}
