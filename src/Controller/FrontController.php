<?php

namespace App\Controller;

use App\Repository\PageRepository;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/{slug}', name: 'app_front_page', methods: ['GET'], requirements: ['slug' => '^(?!admin).*'])]
    public function index(string $slug, PageRepository $pageRepository, SiteRepository $siteRepository): Response
    {
        // Load active site (single active site approach)
        $site = $siteRepository->findOneBy(['isActive' => true]);
        
        if (!$site) {
            throw $this->createNotFoundException('No active site found');
        }

        // Load published page by slug and site
        $page = $pageRepository->findPublishedBySiteAndSlug($slug, $site);
        
        if (!$page) {
            throw $this->createNotFoundException('Page not found or not published');
        }

        // Load sections ordered by position
        $sections = $page->getSections();

        return $this->render('front/page.html.twig', [
            'site' => $site,
            'page' => $page,
            'sections' => $sections,
        ]);
    }
}
