<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Question;
use App\Repository\AnswerSessionRepository;
use App\Repository\ChoiceRepository;
use App\Repository\QuestionnaireRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/questions', name: 'api_questions')]
final class QuestionController extends AbstractController
{
    private const QUESTION_ID_PATH = '/{id}'; //Sonar.
    private const QUESTION_NOT_FOUND = 'Question not found'; //Encore sonar...

    public function __construct(
        private readonly QuestionRepository $questionRepository,
        private readonly QuestionnaireRepository $questionnaireRepository,
        private readonly ChoiceRepository $choiceRepository,
        private readonly AnswerSessionRepository $answerSessionRepository,
        private readonly EntityManagerInterface $entityManager,
    ){}

    /**
     * Question to data
     *
     * @param Question $question
     * @return array
     */
    private function questionToData(Question $question): array
    {
        return [
            'id' => $question->getId(),
            'title' => $question->getTitle(),
            'description' => $question->getDescription(),
            'questionnaireId' => $question->getQuestionnaire()?->getId(),
        ];
    }
    private function questionToNode(Question $question): array
    {
    return [
        'id' => $question->getId(),
        'title' => $question->getTitle(),
        'description' => $question->getDescription(),
        'questionnaireId' => $question->getQuestionnaire()?->getId(),
        'choices' => array_map(
            function (Choice $choice) {
                return [
                    'id' => $choice->getId(),
                    'content' => $choice->getContent(),
                    'nextQuestion' => $choice->getNextQuestion()
                        ? $this->questionToNode($choice->getNextQuestion())
                        : null,
                ];
            },
            $question->getChoices()->toArray()
        ),
    ];
    }

    #[Route('', name: 'list_questions', methods:['GET'])]
    public function list(): JsonResponse
    {
        $questions = $this->questionRepository->findAll();
        $data = array_map(fn (Question $q) => $this->questionToData($q), $questions);
        return $this->json(['data' => $data]);
    }

    #[Route(path: self::QUESTION_ID_PATH, name: 'question', methods: ['GET'])]
    public function question(int $id): JsonResponse{
        $question = $this->questionRepository->find($id);
        if(!$question){
            return $this->json(['error' => self::QUESTION_NOT_FOUND], 404);
        }
        $data = $this->questionToData($question);
        return $this->json(data: ['data' => $data]);
    }

    #[Route(path: '', name: 'post_question', methods: ['POST'])]
    public function postQuestion(Request $request): JsonResponse{
        $body = json_decode($request->getContent(), true) ?? [];
        $title = $body['title'] ?? null;
        $description = $body['description'] ?? null;
        $questionnaireId = $body['questionnaireId'] ?? null;

        if(!$title || !$questionnaireId){
            return $this->json(['error' => 'Invalid form'], 405);
        }

        $questionnaire = $this->questionnaireRepository->find($questionnaireId);
        if(!$questionnaire){
            return $this->json(['error' => 'Questionnaire not found'], 405);
        }

        $question = new Question();
        $question->setTitle($title);
        $question->setDescription($description);
        $questionnaire->addQuestion($question);
        $question->setQuestionnaire($questionnaire);

        if($questionnaire->getRootQuestion() === null){ //Ajout de la question root.
            $question->setIsRoot(true); //On met le flag
            $questionnaire->setRootQuestion($question);
        }

        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $data = $this->questionToData($question);

        return $this->json(['data' => $data], 201);

    }

    #[Route(path: self::QUESTION_ID_PATH, name: 'put_question', methods: ['PUT'])]
    public function putQuestion(int $id, Request $request): JsonResponse{
        $question = $this->questionRepository->find($id);
        if(!$question){
            return $this->json(['error' => self::QUESTION_NOT_FOUND], 404);
        }
        $body = json_decode($request->getContent(), true) ?? [];
        if(array_key_exists('title', $body)){
            $question->setTitle($body['title']);
        }
        if(array_key_exists('description', $body)){
            $question->setDescription($body['description']);
        }
        if(array_key_exists('questionnaireId', $body)){
            $questionnaire = $this->questionnaireRepository->find($body['questionnaireId']);
            if(!$questionnaire){
                return $this->json(['error' => 'Invalide questionnaire'], 405);
            }
            $questionnaire->addQuestion($question);
        }
        $this->entityManager->flush();
        $data = $this->questionToData($question);
        return $this->json(['data' => $data]);
    }

    #[Route(self::QUESTION_ID_PATH, name: 'delete_question', methods: ['DELETE'])]
    public function deleteQuestion(int $id): JsonResponse
    {
        $question = $this->questionRepository->find($id);

        if (!$question) {
            return $this->json(
                ['error' => self::QUESTION_NOT_FOUND],
                404
            );
        }

        // 1) Si cette question est la root du questionnaire, on la retire
        $questionnaire = $question->getQuestionnaire();
        if ($questionnaire !== null && $questionnaire->getRootQuestion() === $question) {
            $questionnaire->setRootQuestion(null);
        }

        // 2) Tous les Choice qui pointent vers CETTE question via nextQuestion
        $choicesPointingHere = $this->choiceRepository->findBy([
            'nextQuestion' => $question,
        ]);

        foreach ($choicesPointingHere as $choice) {
            $choice->setNextQuestion(null);
        }

        // 3) Toutes les AnswerSession
        $sessions = $this->answerSessionRepository->findBy([
            'currentQuestion' => $question,
        ]);

        foreach ($sessions as $session) {
            $session->setCurrentQuestion(null);
        }

        // 4) Suppression de la question
        $this->entityManager->remove($question);
        $this->entityManager->flush();

        // 204 = No Content => corps vide
        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{id}/tree', name: 'questionTree', methods: ['GET'])]
    public function questionTree(int $id): JsonResponse{
    $question = $this->questionRepository->find($id);

    if (!$question) {
        return $this->json(
            ['error' => self::QUESTION_NOT_FOUND],
            404
        );
    }

    $data = $this->questionToNode($question);

    return $this->json(['data' => $data]);
}
}
