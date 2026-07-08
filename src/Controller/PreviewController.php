<?php

namespace App\Controller;

use App\Entity\Page;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/preview')]
#[IsGranted('ROLE_USER')]
class PreviewController extends AbstractController
{
    #[Route('/page/{id}', name: 'app_preview_page', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function page(int $id, PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findWithSections($id);

        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('front/page.html.twig', [
            'page' => $page,
            'site' => $page->getSite(),
        ]);
    }
}
