<?php

namespace App\Controller;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class FrontController extends AbstractController
{
    #[Route('/{slug}', name: 'app_front_page', methods: ['GET'])]
    public function index(string $slug, PageRepository $pageRepository, SiteRepository $siteRepository): Response
    {
        // Get active site (in a real scenario, you'd use domain detection)
        $site = $siteRepository->findOneBy(['isActive' => true]);
        
        if (!$site) {
            throw $this->createNotFoundException('No active site found');
        }

        $page = $pageRepository->findPublishedBySiteAndSlug($slug, $site);
        
        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('front/page.html.twig', [
            'page' => $page,
            'site' => $site,
        ]);
    }
}
