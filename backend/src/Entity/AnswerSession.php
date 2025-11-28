<?php

namespace App\Entity;

use App\Enum\AnswerSessionStatus;
use App\Repository\AnswerSessionRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: AnswerSessionRepository::class)]
class AnswerSession
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy:"CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?Uuid $id = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'answerSessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Questionnaire $questionnaire = null;

    #[ORM\ManyToOne]
    private ?Question $currentQuestion = null;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'answerSession', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $answers;

    #[ORM\Column(length: 255)]
    #[ORM\JoinColumn(nullable: false)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', enumType: AnswerSessionStatus::class)]
    #[ORM\JoinColumn(nullable: false)]
    private AnswerSessionStatus $status;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->answers = new ArrayCollection();
        $this->status = AnswerSessionStatus::IN_PROGRESS;
    }

    public function getStatus(): AnswerSessionStatus
    {
        return $this->status;
    }

    public function setStatusFinished(): static
    {
        $this->status = AnswerSessionStatus::FINISHED;

        return $this;
    }
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getQuestionnaire(): ?Questionnaire
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(?Questionnaire $questionnaire): static
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    public function getCurrentQuestion(): ?Question
    {
        return $this->currentQuestion;
    }

    public function setCurrentQuestion(?Question $currentQuestion): static
    {
        $this->currentQuestion = $currentQuestion;

        return $this;
    }

    /**
     * @return Collection<int, Answer>
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): static
    {
        if (!$this->answers->contains($answer)) {
            $this->answers->add($answer);
            $answer->setAnswerSession($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if (
            $this->answers->removeElement($answer)
            && $answer->getAnswerSession() === $this
        ) {
            $answer->setAnswerSession(null);
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

}
