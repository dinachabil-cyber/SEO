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

        $page = $pageRepository->findOneBy(['slug' => $slug, 'site' => $site, 'isPublished' => true]);
        
        if (!$page) {
            throw $this->createNotFoundException('Page not found');
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
