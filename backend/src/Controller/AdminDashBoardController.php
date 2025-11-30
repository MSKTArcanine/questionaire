<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\AnswerSession;
use App\Entity\Questionnaire;
use App\Enum\AnswerSessionStatus;
use App\Repository\AnswerRepository;
use App\Repository\AnswerSessionRepository;
use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin', name: 'api_admin_')]
final class AdminDashboardController extends AbstractController
{
    public function __construct(
        private readonly QuestionnaireRepository $questionnaireRepository,
        private readonly AnswerSessionRepository $answerSessionRepository,
        private readonly AnswerRepository $answerRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * Liste des questionnaires avec stats globales
     */
    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): JsonResponse
    {
        $questionnaires = $this->questionnaireRepository->findAll();

        $data = [];

        /** @var Questionnaire $q */
        foreach ($questionnaires as $q) {
            $totalSessions    = $this->answerSessionRepository->count([
                'questionnaire' => $q,
            ]);

            $finishedSessions = $this->answerSessionRepository->count([
                'questionnaire' => $q,
                'status'        => AnswerSessionStatus::FINISHED,
            ]);

            //TODO: SI CA FOIRE C EST LA LE SOUCIS
            $completionRate = $totalSessions > 0
                ? $finishedSessions / $totalSessions
                : 0.0;

            $data[] = [
                'id'                => $q->getId(),
                'title'             => $q->getTitle(),
                'description'       => $q->getDescription(),
                'total_sessions'    => $totalSessions,
                'finished_sessions' => $finishedSessions,
                'completion_rate'   => $completionRate,
            ];
        }

        return $this->json(['data' => $data]);
    }

    // Toutes les stats qu'un questionnaire.
    #[Route('/questionnaires/{id}/stats', name: 'questionnaire_stats', methods: ['GET'])]
    public function questionnaireStats(int $id): JsonResponse
    {
        /** @var Questionnaire|null $questionnaire */
        $questionnaire = $this->questionnaireRepository->find($id);

        if (!$questionnaire) {
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }

        /** @var Answer[] $answers */
        $answers = $this->answerRepository->createQueryBuilder('a')
            ->join('a.answerSession', 's')
            ->join('a.question', 'q')
            ->join('a.choice', 'c')
            ->andWhere('s.questionnaire = :questionnaire')
            ->setParameter('questionnaire', $questionnaire)
            ->getQuery()
            ->getResult();

        $questions = [];
        //Ptet faire un helper ?
        foreach ($answers as $answer) {
            $q      = $answer->getQuestion();
            $choice = $answer->getChoice();

            if (!$q || !$choice) {
                continue;
            }

            $qId = $q->getId();
            $cId = $choice->getId();

            if (!isset($questions[$qId])) {
                $questions[$qId] = [
                    'id' => $qId,
                    'title' => $q->getTitle(),
                    'totalAnswers' => 0,
                    'choices' => [],
                ];
            }

            if (!isset($questions[$qId]['choices'][$cId])) {
                $questions[$qId]['choices'][$cId] = [
                    'id'      => $cId,
                    'content' => $choice->getContent(),
                    'count'   => 0,
                ];
            }

            $questions[$qId]['choices'][$cId]['count']++;
            $questions[$qId]['totalAnswers']++;
        }

        // On reloop pour les ID des questionnaires.
        foreach ($questions as &$qStat) {
            $qStat['choices'] = array_values($qStat['choices']);
        }

        return $this->json([
            'questionnaire' => [
                'id' => $questionnaire->getId(),
                'title' => $questionnaire->getTitle(),
            ],
            'questions' => array_values($questions),
        ]);
    }

    /**
     * Export CSV des réponses d'un questionnaire
     */
    #[Route('/questionnaires/{id}/export', name: 'questionnaire_export', methods: ['GET'])]
    public function exportCsv(int $id): Response
    {
        /** @var Questionnaire|null $questionnaire */
        $questionnaire = $this->questionnaireRepository->find($id);

        if (!$questionnaire) {
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }

        /** @var Answer[] $answers */
        $answers = $this->answerRepository->createQueryBuilder('a')
            ->join('a.answerSession', 's')
            ->join('a.question', 'q')
            ->join('a.choice', 'c')
            ->andWhere('s.questionnaire = :questionnaire')
            ->setParameter('questionnaire', $questionnaire)
            ->getQuery()
            ->getResult();

        //SI CA FOIRE, C'EST LA AUSSI.
        $handle = fopen('php://temp', 'r+');

        //La magie du copier coller de "fputcsv", pire nom.
        fputcsv($handle, [
            'session_id',
            'email',
            'question_title',
            'choice_content',
            'status',
        ], ';');

        foreach ($answers as $answer) {
            $session = $answer->getAnswerSession();
            $question = $answer->getQuestion();
            $choice   = $answer->getChoice();

            fputcsv($handle, [
                $session?->getId(),
                $session?->getEmail(),
                $question?->getTitle(),
                $choice?->getContent(),
                $session?->getStatus()->value ?? '',
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $filename = sprintf('questionnaire_%d_answers.csv', $questionnaire->getId());

        return new Response(
            $csv,
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}
