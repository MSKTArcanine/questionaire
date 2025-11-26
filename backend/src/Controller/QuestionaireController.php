<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/questionaire', name: 'api_questionaire')]
final class QuestionaireController extends AbstractController
{
    #[Route('', name: 'get_questionaire', methods:['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['message' => 'index_questionaire']);
    }

    #[Route("/{id}", name: "show_questionaire_id", methods: ['GET'])]
    public function getQuestionaire(int $id){
        return $this->json(["message" => "$id"]);
    }
}
