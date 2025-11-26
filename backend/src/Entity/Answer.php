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
    private ?AnswerSession $answerSession = null;

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

}
