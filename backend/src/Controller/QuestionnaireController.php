<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Repository\QuestionnaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/questionnaire', name: 'api_questionnaire')]
final class QuestionnaireController extends AbstractController
{
    public function __construct(
        private readonly QuestionnaireRepository $questionnaireRepository
    )
    {}
    #[Route('/', name: 'list_questionnaire', methods:['GET'])]
    public function list(): JsonResponse
    {
        $questionnaires = $this->questionnaireRepository->findAll();
        $data = array_map(static fn (Questionnaire $q) => [
            'id' => $q->getId(),
            'title' => $q->getTitle(),
            'description' => $q->getDescription(),
            'rootQuestionId' => $q->getRootQuestion()?->getId(),
        ], $questionnaires);
        return $this->json(['data' => $data]);
    }

    #[Route("/{id}", name: "questionnaire", methods: ['GET'])]
    public function arbre(int $id): JsonResponse{
        /**
         * @var Questionnaire|null $questionnaire
         */
        $questionnaire = $this->questionnaireRepository->find($id);
        if(!$questionnaire){
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }
        //Faut : Les questions + les choix avec la question d'après.
        //Recup tout sous questionnaire avec les infos.
        $questions = [];
        /**
         * @var Question $question
         */
        foreach ($questionnaire->getQuestions() as $question) {
            $choices = [];
            /**
             * @var Choice $choice
             */
            foreach ($question->getChoices() as $choice){
                $choices[] = [
                    'id' => $choice->getId(),
                    'content' => $choice->getContent(),
                    'nextQuestionId' => $choice->getNextQuestion()?->getId(),
                ];
            }

            $questions[] = [
                'id' => $question->getId(),
                'title' => $question->getTitle(),
                'description' => $question->getDescription(),
                'choices' => $choices,
            ];
        }

        $data = [
            'id' => $questionnaire->getId(),
            'title' => $questionnaire->getTitle(),
            'description' => $questionnaire->getDescription(),
            'rootQuestionId' => $questionnaire->getRootQuestion()?->getId(),
            'questions' => $questions,
        ];

        return $this->json($data);
    }
}
