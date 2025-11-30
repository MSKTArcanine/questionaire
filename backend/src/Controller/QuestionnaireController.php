<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Question;
use App\Entity\Questionnaire;
use App\Enum\QuestionType;
use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/questionnaires', name: 'api_questionnaire')]
final class QuestionnaireController extends AbstractController
{
    public function __construct(
        private readonly QuestionnaireRepository $questionnaireRepository
    )
    {}

    private function questionToData(Question $question): array
    {
        // Choices
        $choices = [];
        foreach ($question->getChoices() as $choice) {
            /** @var Choice $choice */
            $choices[] = [
                'id' => $choice->getId(),
                'content' => $choice->getContent(),
                'nextQuestionId' => $choice->getNextQuestion()?->getId(),
                'questionId' => $choice->getQuestion()->getId(),
            ];
        }

        // Media
        $media = $question->getQuestionMedia();
        $mediaData = null;

        if ($media !== null) {
            $mediaData = [
                'id' => $media->getId(),
                'type' => $media->getType()->value,
                'mediaName' => $media->getMediaName(),
                'mimeType' => $media->getMimeType()->value,
                'altText' => $media->getAltText(),
            ];
        }

        return [
            'id' => $question->getId(),
            'title' => $question->getTitle(),
            'description' => $question->getDescription(),
            'type' => $question->getType()?->value ?? QuestionType::RADIO->value,
            'questionnaireId' => $question->getQuestionnaire()->getId(),
            'media' => $mediaData,
            'choices' => $choices,
        ];
    }
    #[Route('', name: 'list_questionnaire', methods:['GET'])]
    public function list(): JsonResponse
    {
        $questionnaires = $this->questionnaireRepository->findAll();
        $data = array_map(static fn (Questionnaire $q) => [
            'id' => $q->getId(),
            'title' => $q->getTitle(),
            'description' => $q->getDescription(),
            'rootQuestionId' => $q->getRootQuestion()?->getId(),
            'slug' => (string) $q->getPublicId(),
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
            $questions[] = $this->questionToData($question);
        }

        $data = [
            'id' => $questionnaire->getId(),
            'title' => $questionnaire->getTitle(),
            'description' => $questionnaire->getDescription(),
            'rootQuestionId' => $questionnaire->getRootQuestion()?->getId(),
            'slug' => (string) $questionnaire->getPublicId(),
            'questions' => $questions,
        ];

        return $this->json(['data' => $data], 200);
    }

    #[Route('', name: 'post_questionnaire', methods:['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true);

        if(!is_array($body)){
            return $this->json(['error' => 'Invalid JSON'], 400);
        }

        $title = $body['title'] ?? null;
        $description = $body['description'] ?? null;

        if($title === '' || $title === null){
            return $this->json(['error' => 'Title is required'], 400);
        }

        $questionnaire = new Questionnaire();
        $questionnaire->setTitle($title);
        $questionnaire->setDescription($description ?? null);
        $em->persist($questionnaire);
        $em->flush();
        $data = [
            'id' => $questionnaire->getId(),
            'title' => $questionnaire->getTitle(),
            'description' => $questionnaire->getDescription(),
            'rootQuestionId' => null,
        ];
        return $this->json(['data' => $data], 201);
    }

    #[Route(path: '/{id}', name: 'deleteQuestionnaire', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse{
        /**
         * @var Questionnaire|null $questionnaire
         */
        $questionnaire = $this->questionnaireRepository->find($id);
        if(!$questionnaire){
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }

        $em->remove($questionnaire);
        $em->flush();

        return $this->json([], 204);
    }
}
