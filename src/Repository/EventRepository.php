<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findByMonth(int $year, int $month, ?int $siteId = null): array
    {
        $startDate = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $endMonth = $month + 1;
        $endYear = $year;
        if ($endMonth > 12) {
            $endMonth = 1;
            $endYear = $year + 1;
        }
        $endDate = sprintf('%04d-%02d-01 00:00:00', $endYear, $endMonth);
        
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.startAt < :endDate')
            ->andWhere('e.endAt >= :startDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.startAt', 'ASC');

        if ($siteId !== null) {
            $qb->andWhere('e.site = :siteId')
               ->setParameter('siteId', $siteId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByDay(int $year, int $month, int $day, ?int $siteId = null): array
    {
        $startDate = sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day);
        $endDate = sprintf('%04d-%02d-%02d 23:59:59', $year, $month, $day);
        
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.startAt >= :startDate')
            ->andWhere('e.startAt <= :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('e.startAt', 'ASC');

        if ($siteId !== null) {
            $qb->andWhere('e.site = :siteId')
               ->setParameter('siteId', $siteId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findUpcoming(?int $siteId = null, int $limit = 10): array
    {
        $now = new \DateTimeImmutable();
        
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.startAt >= :now')
            ->setParameter('now', $now)
            ->orderBy('e.startAt', 'ASC')
            ->setMaxResults($limit);

        if ($siteId !== null) {
            $qb->andWhere('e.site = :siteId')
               ->setParameter('siteId', $siteId);
        }

        return $qb->getQuery()->getResult();
    }

    public function findBySite(int $siteId): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.site = :siteId')
            ->setParameter('siteId', $siteId)
            ->orderBy('e.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Event
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByAssignedUser(int $userId): array
    {
        return $this->createQueryBuilder('e')
            ->join('e.assignedUsers', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('e.startAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
