<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\AnswerSession;
use App\Entity\Choice;
use App\Entity\Question;
use App\Enum\AnswerSessionStatus;
use App\Repository\AnswerSessionRepository;
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
        private readonly AnswerSessionRepository $answerSessionRepository,
        private readonly ChoiceRepository $choiceRepository,
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

    #[Route(path: '/{slug}/answers', name: 'postAnswers', methods: ['POST'])]
    public function postAnswers(string $slug, Request $request):JsonResponse{
        $body = json_decode($request->getContent(), true);
        $choiceId = $body['choiceId'] ?? null;
        if($choiceId === null){
            return $this->json(['error' => 'ChoiceId is required'], 400);
        }

        /**
         * @var AnswerSession | null $answerSession
         */
        $answerSession = $this->answerSessionRepository->find($slug);

        if(!$answerSession){
            return $this->json(['error' => 'AnswerSession not found'], 404);
        }

        if($answerSession->getStatus() === AnswerSessionStatus::FINISHED){
            return $this->json(['error' => 'AnswerSession is already finished'], 409);
        }

        $currentQuestion = $answerSession->getCurrentQuestion();
        if(!$currentQuestion){
            return $this->json(['error' => 'Current question not found'], 500);
        }

        /**
         * @var Choice | null $choice
         */
        $choice = $this->choiceRepository->find($choiceId);
        if(!$choice){
            return $this->json(['error' => 'Choice not found'], 404);
        }

        if($choice->getQuestion()?->getId() !== $currentQuestion->getId()){
            return $this->json(['error' => 'Choice not for currentQuestion'], 400);
        }

        // Creation de l'answer
        $answer = new Answer();
        $answer->setAnswerSession($answerSession);
        $answer->setQuestion($currentQuestion);
        $answer->setChoice($choice);

        $answerSession->addAnswer($answer);

        // La ça marche
        $nextQuestion = $choice->getNextQuestion();
        if($nextQuestion !== null){
            $answerSession->setCurrentQuestion($nextQuestion);
        }

        if($nextQuestion !== null){
            $answerSession->setCurrentQuestion($nextQuestion);
        } else {
            $answerSession->setCurrentQuestion(null);
            $answerSession->setStatusFinished();
        }

        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        return $this->json(['data' => $this->formatSession($answerSession)], 200);
    }

    #[Route(path: '/{id}', name: 'getAS', methods: ['GET'])]
    public function getAnswerSession(string $id): JsonResponse {
        /**
         * @var AnswerSession | null $answerSession
         */
        $answerSession = $this->answerSessionRepository->find($id);

        if(!$answerSession){
            return $this->json(['error' => 'AnswerSession not found'], 404);
        }

        return $this->json(['data' => $this->formatSession($answerSession)], 200);
    }
}
