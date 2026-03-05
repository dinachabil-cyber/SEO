<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\PageSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PageSection>
 */
class PageSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PageSection::class);
    }

    public function findMaxPositionByPage(Page $page): int
    {
        $result = $this->createQueryBuilder('ps')
            ->select('MAX(ps.position)')
            ->where('ps.page = :page')
            ->setParameter('page', $page)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (int)$result : -1;
    }

    /**
     * @return PageSection[] Returns an array of PageSection objects ordered by position
     */
    public function findByPageOrdered(Page $page): array
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.page = :page')
            ->setParameter('page', $page)
            ->orderBy('ps.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPreviousSection(PageSection $section): ?PageSection
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.page = :page')
            ->andWhere('ps.position < :position')
            ->setParameter('page', $section->getPage())
            ->setParameter('position', $section->getPosition())
            ->orderBy('ps.position', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNextSection(PageSection $section): ?PageSection
    {
        return $this->createQueryBuilder('ps')
            ->where('ps.page = :page')
            ->andWhere('ps.position > :position')
            ->setParameter('page', $section->getPage())
            ->setParameter('position', $section->getPosition())
            ->orderBy('ps.position', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
