<?php

namespace App\Entity;

use App\Repository\QuestionnaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: QuestionnaireRepository::class)]
class Questionnaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, Question>
     */
    #[ORM\OneToMany(targetEntity: Question::class, mappedBy: 'questionnaire', orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['title' => 'ASC'])]
    private Collection $questions;

    /**
     * @var Collection<int, AnswerSession>
     */
    #[ORM\OneToMany(targetEntity: AnswerSession::class, mappedBy: 'questionnaire', orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $answerSessions;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $publicId = null;

    #[ORM\ManyToOne(targetEntity: Question::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Question $rootQuestion = null;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->answerSessions = new ArrayCollection();
        $this->publicId = Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): static
    {
        if (!$this->questions->contains($question)) {
            $this->questions->add($question);
            $question->setQuestionnaire($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): static
    {
        if ($this->questions->removeElement($question) && $question->getQuestionnaire() === $this) {
            // set the owning side to null (unless already changed)
            $question->setQuestionnaire(null);
        }

        return $this;
    }

    /**
     * @return Collection<int, AnswerSession>
     */
    public function getAnswerSessions(): Collection
    {
        return $this->answerSessions;
    }

    public function addAnswerSession(AnswerSession $answerSession): static
    {
        if (!$this->answerSessions->contains($answerSession)) {
            $this->answerSessions->add($answerSession);
            $answerSession->setQuestionnaire($this);
        }

        return $this;
    }

    public function removeAnswerSession(AnswerSession $answerSession): static
    {
        if ($this->answerSessions->removeElement($answerSession) && $answerSession->getQuestionnaire() === $this) {
            // set the owning side to null (unless already changed)
            $answerSession->setQuestionnaire(null);
        }

        return $this;
    }

    public function getPublicId(): ?Uuid
    {
        return $this->publicId;
    }

    public function setPublicId(Uuid $publicId): static
    {
        $this->publicId = $publicId;

        return $this;
    }

public function getRootQuestion(): ?Question
{
    return $this->rootQuestion;
}

public function setRootQuestion(?Question $rootQuestion): static
{
    $this->rootQuestion = $rootQuestion;

    return $this;
}
}
