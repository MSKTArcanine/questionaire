<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\AnswerSession;
use App\Entity\Choice;
use App\Entity\Question;
use App\Entity\QuestionMedia;
use App\Entity\Questionnaire;
use App\Entity\User;
use App\Enum\AnswerSessionStatus;
use App\Enum\QuestionType;
use App\Repository\AnswerSessionRepository;
use App\Repository\ChoiceRepository;
use App\Repository\QuestionnaireRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/sessions', name: 'api_choices_')]
final class AnswerSessionController extends AbstractController
{
    public function __construct(
        private readonly QuestionnaireRepository $questionnaireRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly AnswerSessionRepository $answerSessionRepository,
        private readonly ChoiceRepository $choiceRepository,
    ) {}

    private function mediaToJson(?QuestionMedia $media): ?array
    {
        if (!$media) {
            return null;
        }

        return [
            'id'        => $media->getId(),
            'type'      => $media->getType()->value,
            'mediaName' => $media->getMediaName(),
            'mimeType'  => $media->getMimeType()->value,
            'altText'   => $media->getAltText(),
            'streamUrl' => $this->generateUrl(
                'api_questions_stream_media',              // <- name = "api_questions_" + "stream_media"
                ['id' => $media->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ];
    }

    private function formatSession(AnswerSession $session): array
    {
        $questionnaire   = $session->getQuestionnaire();
        /**
         * @var Question $currentQuestion
         */
        $currentQuestion = $session->getCurrentQuestion();
        return [
            'id' => (string) $session->getId(),   // UUID de la session
            'questionnaire' => [
                'id'    => (string) $questionnaire->getPublicId(),
                'title' => $questionnaire->getTitle(),
                'description' => $questionnaire->getDescription(),
            ],
            'current_question' => $currentQuestion ? [
                'id'    => $currentQuestion->getId(),
                'title' => $currentQuestion->getTitle(),
                'description'  => $currentQuestion->getDescription(),
                'type' => $currentQuestion->getType()?->value ?? QuestionType::RADIO->value,
                'media' => $this->mediaToJson($currentQuestion->getQuestionMedia()),
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

        //ON CHOPPE L USER DU COUP JWT

        /**
         * @var User|null $user;
         */
        $user = $this->getUser();
        if($user === null){
            return $this->json(['error' => 'Unauthorized'], 401);
        }
        $email = $user->getUserIdentifier();
        //.

        $body = json_decode($request->getContent(), true);
        $publicId = $body['slug'] ?? null;
        
        if($publicId === null){
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }

        $questionnaire = $this->questionnaireRepository->findOneBy(['publicId' => $publicId]);
        if(!$questionnaire){
            return $this->json(['error' => 'Questionnaire not found'], 404);
        }

        //Donc là, on fait BIEN gaffe à récup la DERNIERE session.
        $existingSession = $this->answerSessionRepository->findLatestIncompleteByQuestionnaireAndEmail($questionnaire, $email);
        if($existingSession !== null){
            return $this->json(['data' => $this->formatSession($existingSession)], 200);
        }//Si y en a pas, on fait comme si de rien était.

        $rootQuestion = $questionnaire->getRootQuestion();
        if(!$rootQuestion){
            return $this->json(['error' => 'Root question not found'], 500); //500 car erreur admin, mais l'user est pas censé savoir hein.
        }

        $answerSession = new AnswerSession();
        $answerSession->setQuestionnaire($questionnaire);
        $answerSession->setCurrentQuestion($rootQuestion);
        //On balance le mail via l'user:
        $answerSession->setEmail($email);
        //bam
        $this->entityManager->persist($answerSession);
        $this->entityManager->flush();

        return $this->json(['data' => $this->formatSession($answerSession)], 201);
        
    }

    #[Route(path: '/{slug}/answers', name: 'postAnswers', methods: ['POST'])]
    public function postAnswers(string $slug, Request $request):JsonResponse{
        // parse incoming request to extract choiceId and file
        [$choiceId, $uploadedFile] = $this->parseChoiceIdAndFile($request);

        if ($choiceId === null) {
            return $this->json(['error' => 'ChoiceId is required'], 400);
        }

        // find the session
        /** @var AnswerSession|null $answerSession */
        $answerSession = $this->answerSessionRepository->find($slug);
        if (!$answerSession) {
            return $this->json(['error' => 'AnswerSession not found'], 404);
        }

        // finished check
        if ($answerSession->getStatus() === AnswerSessionStatus::FINISHED) {
            return $this->json(['error' => 'AnswerSession is already finished'], 409);
        }

        // current question check
        $currentQuestion = $answerSession->getCurrentQuestion();
        if (!$currentQuestion) {
            return $this->json(['error' => 'Current question not found'], 500);
        }

        // validate choice exists and belongs to current question
        /** @var Choice|null $choice */
        $choice = $this->choiceRepository->find($choiceId);
        if (!$choice) {
            return $this->json(['error' => 'Choice not found'], 404);
        }
        if ($choice->getQuestion()?->getId() !== $currentQuestion->getId()) {
            return $this->json(['error' => 'Choice not for currentQuestion'], 400);
        }

        // create Answer
        $answer = $this->createAnswer($answerSession, $currentQuestion, $choice);

        // if there's an uploaded file, process it
        if ($uploadedFile instanceof UploadedFile) {
            $errorResp = $this->handleUploadedFile($uploadedFile, $answerSession, $answer);
            if ($errorResp instanceof JsonResponse) {
                return $errorResp;
            }
        }

        $answerSession->addAnswer($answer);

        // move to next question or finish
        $nextQuestion = $choice->getNextQuestion();
        if ($nextQuestion !== null) {
            $answerSession->setCurrentQuestion($nextQuestion);
        } else {
            $answerSession->setCurrentQuestion(null);
            $answerSession->setStatusFinished();
        }

        $this->entityManager->persist($answer);
        $this->entityManager->flush();

        return $this->json(['data' => $this->formatSession($answerSession)], 200);
    }

    private function parseChoiceIdAndFile(Request $request): array
    {
        $contentType = $request->headers->get('Content-Type', '');
        $choiceId = null;
        $uploadedFile = null;

        if (str_starts_with((string) $contentType, 'application/json')) {
            $body = json_decode($request->getContent(), true) ?? [];
            $choiceId = $body['choiceId'] ?? null;
        } else {
            $choiceId = $request->request->get('choiceId');
            $uploadedFile = $request->files->get('file');
        }

        return [$choiceId, $uploadedFile];
    }

    private function createAnswer(AnswerSession $answerSession, Question $currentQuestion, Choice $choice): Answer
    {
        $answer = new Answer();
        $answer->setAnswerSession($answerSession);
        $answer->setQuestion($currentQuestion);
        $answer->setChoice($choice);
        return $answer;
    }

    private function handleUploadedFile(UploadedFile $uploadedFile, AnswerSession $answerSession, Answer $answer): ?JsonResponse
    {
        $mime = $uploadedFile->getMimeType() ?? '';
        if (!in_array($mime, ['image/png', 'video/mp4'], true)) {
            return $this->json(['error' => 'Unsupported media type'], 400);
        }

        /** @var string $uploadDir */
        $uploadDir = $this->getParameter('answer_upload_dir');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = $uploadedFile->guessExtension() ?: 'bin';
        $safeBase = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $safeBase);

        $newfilename = sprintf(
            '%s_%s.%s',
            (string) $answerSession->getId(),
            uniqid($safeBase . '_', true),
            $extension
        );

        $uploadedFile->move($uploadDir, $newfilename);

        $publicPath = '/uploads/answers/' . $newfilename;

        $answer->setMediaPath($publicPath);
        $answer->setMediaName($uploadedFile->getClientOriginalName());
        $answer->setMediaMimeType($mime);

        return null;
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
