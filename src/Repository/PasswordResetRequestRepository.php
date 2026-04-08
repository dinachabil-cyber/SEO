<?php

namespace App\Repository;

use App\Entity\PasswordResetRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetRequest>
 */
class PasswordResetRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetRequest::class);
    }

    public function findPendingByUser(int $userId): ?PasswordResetRequest
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :userId')
            ->andWhere('p.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', PasswordResetRequest::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAllPending(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->setParameter('status', PasswordResetRequest::STATUS_PENDING)
            ->orderBy('p.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.requestedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
