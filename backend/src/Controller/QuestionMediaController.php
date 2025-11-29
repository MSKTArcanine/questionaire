<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Question;
use App\Entity\QuestionMedia;
use App\Enum\AllowedMimeType;
use App\Enum\MediaType;
use App\Enum\QuestionType;
use App\Repository\AnswerSessionRepository;
use App\Repository\ChoiceRepository;
use App\Repository\QuestionnaireRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use ValueError;

#[Route('/api/questions', name: 'api_questions')]
final class QuestionMediaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ){}

    #[Route(path: '/{id}/media', name: 'upload_media', methods: ['POST'])]
    public function uploadMedia(int $id, Request $request): JsonResponse
    {
        /** @var Question|null $question */
        $question = $this->entityManager->getRepository(Question::class)->find($id);

        if (!$question) {
            return $this->json(['error' => 'Question not found'], 404);
        }

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            return $this->json(['error' => 'File is required'], 400);
        }

        $altText = $request->request->get('altText') ?: null;

        $mime = $uploadedFile->getMimeType() ?? '';
        
        try {
            $allowedMime = AllowedMimeType::from($mime);
        } catch(ValueError){
            return $this->json(['error' => 'Unsupported media type'], 400);
        }

        $mediaType = match(true){
            str_starts_with($mime, 'image/') => MediaType::IMAGE,
            str_starts_with($mime, 'video/') => MediaType::VIDEO,
            default => throw new Error('Unsupported media category')
        };

        /** @var string $uploadDir */
        $uploadDir = $this->getParameter('question_upload_dir');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $extension = $uploadedFile->guessExtension() ?: 'bin';
        $safeBase = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $safeBase);

        $newFilename = sprintf(
            'q%d_%s.%s',
            $question->getId(),
            uniqid($safeBase . '_', true),
            $extension
        );

        $uploadedFile->move($uploadDir, $newFilename);

        $media = $question->getQuestionMedia() ?? new QuestionMedia();
        $media->setQuestion($question);
        $media->setType($mediaType);

        $media->setMediaName($newFilename);
        $media->setMimeType($allowedMime);
        $media->setAltText($altText);

        $question->setQuestionMedia($media);

        $this->entityManager->persist($media);
        $this->entityManager->flush();

        return $this->json([
            'data' => [
                'id'        => $media->getId(),
                'type'      => $media->getType()->value,
                'mediaName' => $media->getMediaName(), // ou getUri()
                'mimeType'  => $media->getMimeType()->value,
                'altText'   => $media->getAltText(),
            ],
        ], 201);
    }
}
