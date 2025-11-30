<?php

namespace App\Entity;

use App\Enum\AllowedMimeType;
use App\Enum\MediaType;
use App\Repository\QuestionMediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionMediaRepository::class)]
class QuestionMedia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: MediaType::class)]
    private ?MediaType $type = null;

    #[ORM\Column(length: 255)]
    private ?string $mediaName = null;

    #[ORM\Column(enumType: AllowedMimeType::class)]
    private ?AllowedMimeType $mimeType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $altText = null;

    #[ORM\OneToOne(inversedBy: 'questionMedia')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Question $question = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?MediaType
    {
        return $this->type;
    }

    public function setType(MediaType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    public function setMediaName(string $mediaName): static
    {
        $this->mediaName = $mediaName;

        return $this;
    }

    public function getMimeType(): ?AllowedMimeType
    {
        return $this->mimeType;
    }

    public function setMimeType(AllowedMimeType $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): static
    {
        $this->altText = $altText;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): static
    {
        $this->question = $question;

        return $this;
    }
}
