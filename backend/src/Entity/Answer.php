<?php

namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnswerRepository::class)]
class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AnswerSession $answerSession = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Choice $choice = null;

    #[ORM\ManyToOne(inversedBy: 'answers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mediaPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mediaName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $mediaMimeType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnswerSession(): ?AnswerSession
    {
        return $this->answerSession;
    }

    public function setAnswerSession(?AnswerSession $answerSession): static
    {
        $this->answerSession = $answerSession;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getChoice(): ?Choice
    {
        return $this->choice;
    }

    public function setChoice(?Choice $choice): static
    {
        $this->choice = $choice;

        return $this;
    }

    public function getMediaPath(): ?string
    {
        return $this->mediaPath;
    }

    public function setMediaPath(?string $mediaPath): static
    {
        $this->mediaPath = $mediaPath;

        return $this;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    public function setMediaName(?string $mediaName): static
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    public function getMediaMimeType(): ?string
    {
        return $this->mediaMimeType;
    }

    public function setMediaMimeType(?string $mediaMimeType): static
    {
        $this->mediaMimeType = $mediaMimeType;

        return $this;
    }

}
