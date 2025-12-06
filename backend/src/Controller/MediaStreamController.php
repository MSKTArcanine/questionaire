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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use ValueError;

#[Route('/api/questions', name: 'api_questions_')]
final class MediaStreamController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ){}

    #[Route(path: '/{id}/stream', name: 'stream_media', methods: ['GET'])]
    public function streamMedia(int $id, Request $request): Response
    {
        // 1. Récupérer QuestionMedia
        $media = $this->entityManager->getRepository(QuestionMedia::class)->find($id);
        if (!$media) {
            return $this->json(['error' => 'Media not found'], 404);
        }

        // 2. Construire le chemin...
        /** @var string $uploadDir */
        $uploadDir = $this->getParameter('question_upload_dir');
        $filePath = $uploadDir . DIRECTORY_SEPARATOR . $media->getMediaName();

        if (!is_file($filePath)) {
            return $this->json(['error' => 'File not found'], 404);
        }

        // 3. Stream = binary
        $response = new BinaryFileResponse($filePath);

        // 4. Content-Type => enum
        $response->headers->set('Content-Type', $media->getMimeType()->value);

        // 5. inline pour le navigateur lo
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $media->getMediaName()
        );

        return $response;
    }
}
