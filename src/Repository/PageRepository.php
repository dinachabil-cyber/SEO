<?php

namespace App\Repository;

use App\Entity\Page;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    /**
     * Find one published page by slug and site
     */
    public function findPublishedBySiteAndSlug(string $slug, Site $site): ?Page
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.site = :site')
            ->andWhere('p.isPublished = true')
            ->setParameter('slug', $slug)
            ->setParameter('site', $site)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Find all pages by site ordered by slug
     *
     * @return Page[] Returns an array of Page objects
     */
    public function findBySiteOrdered(Site $site): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.site = :site')
            ->setParameter('site', $site)
            ->orderBy('p.slug', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
