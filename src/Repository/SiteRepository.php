<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Site>
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    /**
     * Find all active sites
     *
     * @return Site[] Returns an array of Site objects
     */
    public function findActiveSites(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isActive = :val')
            ->setParameter('val', true)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Find one active site by domain
     */
    public function findOneActiveByDomain(?string $domain): ?Site
    {
        if (null === $domain) {
            return null;
        }

        return $this->createQueryBuilder('s')
            ->andWhere('s.domain = :domain')
            ->andWhere('s.isActive = :active')
            ->setParameter('domain', $domain)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
