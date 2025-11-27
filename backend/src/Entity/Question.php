<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
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
     * @var Collection<int, Choice>
     */
    #[ORM\OneToMany(targetEntity: Choice::class, mappedBy: 'question')]
    private Collection $choices;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Questionnaire $questionnaire = null;

    /**
     * @var Collection<int, Questionnaire>
     */
    #[ORM\OneToMany(targetEntity: Questionnaire::class, mappedBy: 'rootQuestion')]
    private Collection $questionnaires;

    /**
     * @var Collection<int, AnswerSession>
     */
    #[ORM\OneToMany(targetEntity: AnswerSession::class, mappedBy: 'currentQuestion')]
    private Collection $answerSessions;

    /**
     * @var Collection<int, Answer>
     */
    #[ORM\OneToMany(targetEntity: Answer::class, mappedBy: 'question', orphanRemoval: true)]
    private Collection $answers;

    public function __construct()
    {
        $this->choices = new ArrayCollection();
        $this->questionnaires = new ArrayCollection();
        $this->answerSessions = new ArrayCollection();
        $this->answers = new ArrayCollection();
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
     * @return Collection<int, Choice>
     */
    public function getChoices(): Collection
    {
        return $this->choices;
    }

    public function addChoice(Choice $choice): static
    {
        if (!$this->choices->contains($choice)) {
            $this->choices->add($choice);
            $choice->setQuestion($this);
        }

        return $this;
    }

    public function removeChoice(Choice $choice): static
    {
        if ($this->choices->removeElement($choice) && $choice->getQuestion() === $this) {
            // set the owning side to null (unless already changed)
            $choice->setQuestion(null);
        }

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

    /**
     * @return Collection<int, Questionnaire>
     */
    public function getQuestionnaires(): Collection
    {
        return $this->questionnaires;
    }

    public function addQuestionnaire(Questionnaire $questionnaire): static
    {
        if (!$this->questionnaires->contains($questionnaire)) {
            $this->questionnaires->add($questionnaire);
            $questionnaire->setRootQuestion($this);
        }

        return $this;
    }

    public function removeQuestionnaire(Questionnaire $questionnaire): static
    {
        if ($this->questionnaires->removeElement($questionnaire) && $questionnaire->getRootQuestion() === $this) {
            // set the owning side to null (unless already changed)
            $questionnaire->setRootQuestion(null);
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
            $answerSession->setCurrentQuestion($this);
        }

        return $this;
    }

    public function removeAnswerSession(AnswerSession $answerSession): static
    {
        $removed = $this->answerSessions->removeElement($answerSession);

        if ($removed && $answerSession->getCurrentQuestion() === $this) {
            $answerSession->setCurrentQuestion(null);
        }

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
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): static
    {
        if ($this->answers->removeElement($answer)) {
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
        }

        return $this;
    }
}
