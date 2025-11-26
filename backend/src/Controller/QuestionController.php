<?php

namespace App\Controller;

use App\Entity\Question;
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

    public function __construct(
        private readonly QuestionRepository $questionRepository,
        private readonly QuestionnaireRepository $questionnaireRepository,
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
            return $this->json(['error' => 'Question not found']);
        }
        $data = $this->questionToData($question);
        return $this->json(data: ['data' => $data]);
    }

    #[Route(path: '', name: 'post_question', methods: ['POST'])]
    public function postQuestion(Request $request): JsonResponse{
        $body = json_decode($request->getContent(), true) ?? [];
        $title = $body['title'] ?? null;
        $description = $body['description'] ?? null;
        $questionnaireId = $body['questionnaire'] ?? null;

        if(!$title || !$questionnaireId){
            return $this->json(['error' => 'Invalid form'], 405);
        }

        $questionnaire = $this->questionnaireRepository->find($questionnaireId);
        if(!$questionnaire){
            return $this->json(['error' => 'questionnaire not found'], 405);
        }

        $question = new Question();
        $question->setTitle($title);
        $question->setDescription($description);
        $question->setQuestionnaire($questionnaire);
        $this->entityManager->persist($question);
        $this->entityManager->flush();

        $data = $this->questionToData($question);

        return $this->json(['data' => $data], 201);

    }

    #[Route(path: self::QUESTION_ID_PATH, name: 'put_question', methods: ['PUT'])]
    public function putQuestion(int $id, Request $request): JsonResponse{
        $question = $this->questionRepository->find($id);
        if(!$question){
            return $this->json(['error' => 'Question not found'], 404);
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
                ['error' => 'Question not found'],404
            );
        }

        $this->entityManager->remove($question);
        $this->entityManager->flush();

        return $this->json(['message' => 'ok']);
    }

}
