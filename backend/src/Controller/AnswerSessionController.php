<?php

namespace App\Controller;

use App\Entity\AnswerSession;
use App\Entity\Choice;
use App\Entity\Question;
use App\Enum\AnswerSessionStatus;
use App\Repository\ChoiceRepository;
use App\Repository\QuestionnaireRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/sessions', name: 'api_choices_')]
final class AnswerSessionController extends AbstractController
{
    private const QUESTION_ID_PATH = '/{id}'; //Sonar.
    private const CHOICE_NOT_FOUND = 'Choice not found';
    public function __construct(
        private readonly QuestionnaireRepository $questionnaireRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    private function formatSession(AnswerSession $session): array
    {
        $questionnaire   = $session->getQuestionnaire();
        $currentQuestion = $session->getCurrentQuestion();

        return [
            'id' => (string) $session->getId(),   // UUID de la session
            'questionnaire' => [
                'id'    => (string) $questionnaire->getPublicId(),
                'title' => $questionnaire->getTitle(),
            ],
            'current_question' => $currentQuestion ? [
                'id'    => $currentQuestion->getId(),
                'title' => $currentQuestion->getTitle(),
                'description'  => $currentQuestion->getDescription(),
                'choices' => array_map(
                    static fn(Choice $choice) => [
                        'id'    => $choice->getId(),
                        'content' => $choice->getContent(),
                    ],
                    $currentQuestion->getChoices()->toArray()
                ),
            ] : null,
            'finished' => $session->getStatus() === AnswerSessionStatus::FINISHED,
        ];
    }

    #[Route(path: '', name: 'postAS', methods: ['POST'])]
    public function crateAnswerSession(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $publicId = $body['slug'] ?? null;
        if($publicId === null){
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }

        $questionnaire = $this->questionnaireRepository->findOneBy(['publicId' => $publicId]);
        if(!$questionnaire){
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }

        $rootQuestion = $questionnaire->getRootQuestion();
        if(!$rootQuestion){
            return $this->json(['error' => 'Root question not found'], 500); //500 car erreur admin, mais l'user est pas censé savoir hein.
        }

        $answerSession = new AnswerSession();
        $answerSession->setQuestionnaire($questionnaire);
        $answerSession->setCurrentQuestion($rootQuestion);
        $this->entityManager->persist($answerSession);
        $this->entityManager->flush();

        return $this->json(['data' => $this->formatSession($answerSession)], 201);
        
    }
}
