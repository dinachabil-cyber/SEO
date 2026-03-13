<?php

namespace App\Repository;

use App\Entity\Site;
use App\Entity\User;
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

    public function findFiltered(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.user', 'u')
            ->addSelect('u')
            ->addSelect('COUNT(p.id) AS HIDDEN pageCount')
            ->leftJoin('s.pages', 'p')
            ->groupBy('s.id');

        if (!empty($filters['creator'])) {
            $qb->andWhere('u.name LIKE :creator')
                ->setParameter('creator', '%' . $filters['creator'] . '%');
        }

        if (!empty($filters['technology'])) {
            $qb->andWhere('s.technology LIKE :technology')
                ->setParameter('technology', '%' . $filters['technology'] . '%');
        }

        if (!empty($filters['hosting'])) {
            $qb->andWhere('s.hosting LIKE :hosting')
                ->setParameter('hosting', '%' . $filters['hosting'] . '%');
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('s.status = :status')
                ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['domain'])) {
            $qb->andWhere('s.domain LIKE :domain')
                ->setParameter('domain', '%' . $filters['domain'] . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllWithUser(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.user', 'u')
            ->addSelect('u')
            ->getQuery()
            ->getResult();
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

    /**
     * Find site with all pages and their sections by id
     */
    public function findWithPagesAndSections(int $id): ?Site
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id = :id')
            ->leftJoin('s.pages', 'p')
            ->addSelect('p')
            ->leftJoin('p.sections', 'sec')
            ->addSelect('sec')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
