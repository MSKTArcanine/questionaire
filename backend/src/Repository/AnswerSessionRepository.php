<?php

namespace App\Repository;

use App\Entity\AnswerSession;
use App\Entity\Questionnaire;
use App\Enum\AnswerSessionStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnswerSession>
 */
class AnswerSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnswerSession::class);
    }

    public function findLatestIncompleteByQuestionnaireAndEmail(
    Questionnaire $questionnaire,
    string $email
    ): ?AnswerSession {
    return $this->createQueryBuilder('s')
        ->andWhere('s.questionnaire = :q')
        ->andWhere('s.email = :email')
        ->andWhere('s.status != :finished')
        ->setParameter('q', $questionnaire)
        ->setParameter('email', $email)
        ->setParameter('finished', AnswerSessionStatus::FINISHED)
        ->orderBy('s.createdAt', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
    } //Pour fetch la derniere session.
}
