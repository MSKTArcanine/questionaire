<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Question;
use App\Repository\ChoiceRepository;
use App\Repository\QuestionnaireRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/choices', name: 'api_choices_')]
final class ChoiceController extends AbstractController
{
    private const QUESTION_ID_PATH = '/{id}'; //Sonar.
    private const CHOICE_NOT_FOUND = 'Choice not found';
    public function __construct(
        private readonly ChoiceRepository $choiceRepository,
        private readonly QuestionRepository $questionRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}


    private function choiceToData(Choice $choice): array
    {
        return [
            'id' => $choice->getId(),
            'content' => $choice->getContent(),
            'questionId' => $choice->getQuestion()?->getId(),
            'nextQuestionId' => $choice->getNextQuestion()?->getId(),
        ];
    }

    #[Route('', name: 'list_choices', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $choices = $this->choiceRepository->findAll();

        $data = array_map(
            fn (Choice $c) => $this->choiceToData($c),
            $choices
        );

        return $this->json(['data' => $data]);
    }

    #[Route(self::QUESTION_ID_PATH, name: 'detail', methods: ['GET'])]
    public function detail(int $id): JsonResponse
    {
        $choice = $this->choiceRepository->find($id);

        if (!$choice) {
            return $this->json(
                ['error' => self::CHOICE_NOT_FOUND],
                404
            );
        }

        return $this->json(['data' => $this->choiceToData($choice)]);
    }

    #[Route('', name: 'post_choice', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        $questionId = $body['questionId'] ?? null;
        $content = $body['content'] ?? null;
        $nextQuestionId = $body['nextQuestionId'] ?? null;

        if (!$questionId || !$content) {
            return $this->json(
                ['error' => 'Invalid form'],
                405
            );
        }

        $question = $this->questionRepository->find($questionId);

        if (!$question) {
            return $this->json(
                ['error' => 'Question not found'],
                405
            );
        }

        $choice = new Choice();
        $choice->setContent($content);
        $choice->setQuestion($question);

        if ($nextQuestionId !== null) {
            $nextQuestion = $this->questionRepository->find($nextQuestionId);

            if (!$nextQuestion) {
                return $this->json(
                    ['error' => 'Next question not found'],
                    405
                );
            }

            $choice->setNextQuestion($nextQuestion);
        }

        $this->entityManager->persist($choice);
        $this->entityManager->flush();

        return $this->json(
            ['data' => $this->choiceToData($choice)],
            201
        );
    }

    #[Route(self::QUESTION_ID_PATH, name: 'put_choice', methods: ['PUT'])]
    public function putChoice(int $id, Request $request): JsonResponse
    {
        $choice = $this->choiceRepository->find($id);

        if (!$choice) {
            return $this->json(
                ['error' => self::CHOICE_NOT_FOUND],
                404
            );
        }

        $body = json_decode($request->getContent(), true) ?? [];

        if (array_key_exists('content', $body)) {
            $choice->setContent($body['content']);
        }

        if (array_key_exists('questionId', $body)) {
            $question = $this->questionRepository->find($body['questionId']);

            if (!$question) {
                return $this->json(
                    ['error' => 'Question not found'],
                    405
                );
            }

            $choice->setQuestion($question);
        }

        if (array_key_exists('nextQuestionId', $body)) {
            if ($body['nextQuestionId'] === null) {
                $choice->setNextQuestion(null);
            } else {
                $nextQuestion = $this->questionRepository->find($body['nextQuestionId']);

                if (!$nextQuestion) {
                    return $this->json(
                        ['error' => 'Next question not found'],
                        405
                    );
                }

                $choice->setNextQuestion($nextQuestion);
            }
        }

        $this->entityManager->flush();

        return $this->json(['data' => $this->choiceToData($choice)]);
    }

    #[Route(self::QUESTION_ID_PATH, name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $choice = $this->choiceRepository->find($id);

        if (!$choice) {
            return $this->json(
                ['error' => self::CHOICE_NOT_FOUND],
                404
            );
        }

        $this->entityManager->remove($choice);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }

}
