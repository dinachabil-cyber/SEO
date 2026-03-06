<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Site;
use App\Form\PageType;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/admin/site/{site}/page')]
class PageController extends AbstractController
{
    #[Route('/new', name: 'app_page_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        $page = new Page();
        $page->setSite($site);

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($page);
            $entityManager->flush();

            $this->addFlash('success', 'Page created successfully');

            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $site->getId(),
                'pageId' => $page->getId(),
            ]);
        }

        return $this->render('admin/page/new.html.twig', [
            'page' => $page,
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{pageId}/edit', name: 'app_page_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Site $site,
        int $pageId,
        PageRepository $pageRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $page = $pageRepository->find($pageId);

        if (!$page || $page->getSite()->getId() !== $site->getId()) {
            $this->addFlash('error', 'Page not found');

            return $this->redirectToRoute('app_site_show', [
                'id' => $site->getId(),
            ]);
        }

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Page updated successfully');

            return $this->redirectToRoute('app_site_show', [
                'id' => $site->getId(),
            ]);
        }

        return $this->render('admin/page/edit.html.twig', [
            'page' => $page,
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{pageId}/delete', name: 'app_page_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Site $site,
        int $pageId,
        PageRepository $pageRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $page = $pageRepository->find($pageId);

        if (!$page || $page->getSite()->getId() !== $site->getId()) {
            $this->addFlash('error', 'Page not found');

            return $this->redirectToRoute('app_site_show', [
                'id' => $site->getId(),
            ]);
        }

        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->request->get('_token'))) {
            $entityManager->remove($page);
            $entityManager->flush();

            $this->addFlash('success', 'Page deleted successfully');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_site_show', [
            'id' => $site->getId(),
        ]);
    }
}