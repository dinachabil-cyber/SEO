<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Form\PageSectionType;
use App\Repository\PageRepository;
use App\Repository\PageSectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/site/{siteId}/page/{pageId}')]
class PageBuilderController extends AbstractController
{
    #[Route('/builder', name: 'app_page_builder', methods: ['GET'])]
    public function index(int $siteId, int $pageId, PageRepository $pageRepository): Response
    {
        $page = $pageRepository->find($pageId);
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/page/builder.html.twig', [
            'page' => $page,
            'site' => $page->getSite(),
        ]);
    }

    #[Route('/section/new', name: 'app_section_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $siteId, int $pageId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $pageRepository->find($pageId);
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = new PageSection();
        $section->setPage($page);
        $section->setPosition($pageSectionRepository->findMaxPositionByPage($page) + 1);

        $form = $this->createForm(PageSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($section);
            $entityManager->flush();

            $this->addFlash('success', 'Section added successfully');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]));
        }

        return $this->render('admin/section/new.html.twig', [
            'section' => $section,
            'page' => $page,
            'site' => $page->getSite(),
            'form' => $form,
        ]);
    }

    #[Route('/section/{sectionId}/edit', name: 'app_section_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $pageRepository->find($pageId);
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = $pageSectionRepository->find($sectionId);
        
        if (!$section || $section->getPage()->getId() !== $pageId) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(PageSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Section updated successfully');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]));
        }

        return $this->render('admin/section/edit.html.twig', [
            'section' => $section,
            'page' => $page,
            'site' => $page->getSite(),
            'form' => $form,
        ]);
    }

    #[Route('/section/{sectionId}/delete', name: 'app_section_delete', methods: ['POST'])]
    public function delete(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $pageRepository->find($pageId);
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = $pageSectionRepository->find($sectionId);
        
        if (!$section || $section->getPage()->getId() !== $pageId) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $section->getId(), $request->request->get('_token'))) {
            $entityManager->remove($section);
            $entityManager->flush();
            $this->addFlash('success', 'Section deleted successfully');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
            'siteId' => $siteId,
            'pageId' => $pageId,
        ]));
    }

    #[Route('/section/{sectionId}/up', name: 'app_section_up', methods: ['POST'])]
    public function up(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $pageRepository->find($pageId);
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = $pageSectionRepository->find($sectionId);
        
        if (!$section || $section->getPage()->getId() !== $pageId) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        $previousSection = $pageSectionRepository->findPreviousSection($section);
        
        if ($previousSection) {
            $tempPosition = $section->getPosition();
            $section->setPosition($previousSection->getPosition());
            $previousSection->setPosition($tempPosition);
            
            $entityManager->flush();
            $this->addFlash('success', 'Section moved up successfully');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
            'siteId' => $siteId,
            'pageId' => $pageId,
        ]));
    }

    #[Route('/section/{sectionId}/down', name: 'app_section_down', methods: ['POST'])]
    public function down(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $pageRepository->find($pageId);
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = $pageSectionRepository->find($sectionId);
        
        if (!$section || $section->getPage()->getId() !== $pageId) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        $nextSection = $pageSectionRepository->findNextSection($section);
        
        if ($nextSection) {
            $tempPosition = $section->getPosition();
            $section->setPosition($nextSection->getPosition());
            $nextSection->setPosition($tempPosition);
            
            $entityManager->flush();
            $this->addFlash('success', 'Section moved down successfully');
        }

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
            'siteId' => $siteId,
            'pageId' => $pageId,
        ]));
    }
}
