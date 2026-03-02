<?php

namespace App\Controller;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontController extends AbstractController
{
    #[Route('/{slug}', name: 'app_front_page_show', methods: ['GET'])]
    public function show(string $slug, PageRepository $pageRepository, SiteRepository $siteRepository): Response
    {
        // Get active site (for now, first active site)
        $site = $siteRepository->findOneBy(['isActive' => true]);
        
        if (!$site) {
            throw $this->createNotFoundException('No active site found');
        }

        // Debug info
        $allPages = $pageRepository->findAll();
        $sitePages = $pageRepository->createQueryBuilder('p')
            ->andWhere('p.site = :site')
            ->andWhere('p.isPublished = true')
            ->setParameter('site', $site)
            ->getQuery()
            ->getResult();

        $page = $pageRepository->findOneBySlugAndSite($slug, $site);
        
        if (!$page) {
            throw $this->createNotFoundException(sprintf('Page not found for slug "%s" and site "%s"', $slug, $site->getName()));
        }

        // Get sections ordered by position
        $sections = $page->getSections()->toArray();
        usort($sections, function($a, $b) {
            return $a->getPosition() <=> $b->getPosition();
        });

        return $this->render('front/page_show.html.twig', [
            'page' => $page,
            'sections' => $sections,
        ]);
    }
}
