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

    #[Route('/preview', name: 'app_page_preview', methods: ['GET'])]
    public function preview(int $siteId, int $pageId, PageRepository $pageRepository): Response
    {
        $page = $pageRepository->find($pageId);
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        // Load sections ordered by position
        $sections = $page->getSections();

        return $this->render('front/page.html.twig', [
            'site' => $page->getSite(),
            'page' => $page,
            'sections' => $sections,
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

        // Handle section type restrictions
        $form = $this->createForm(PageSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $type = $section->getType();
            
            // Only one header allowed and must be first
            if ($type === 'header') {
                $existingHeader = $pageSectionRepository->createQueryBuilder('ps')
                    ->where('ps.page = :page')
                    ->andWhere('ps.type = :type')
                    ->setParameter('page', $page)
                    ->setParameter('type', 'header')
                    ->getQuery()
                    ->getOneOrNullResult();
                
                if ($existingHeader) {
                    $this->addFlash('error', 'Only one header section is allowed per page');
                    return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
                        'siteId' => $siteId,
                        'pageId' => $pageId,
                    ]));
                }
                $section->setPosition(1);
            } 
            // Only one footer allowed and must be last
            elseif ($type === 'footer') {
                $existingFooter = $pageSectionRepository->createQueryBuilder('ps')
                    ->where('ps.page = :page')
                    ->andWhere('ps.type = :type')
                    ->setParameter('page', $page)
                    ->setParameter('type', 'footer')
                    ->getQuery()
                    ->getOneOrNullResult();
                
                if ($existingFooter) {
                    $this->addFlash('error', 'Only one footer section is allowed per page');
                    return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
                        'siteId' => $siteId,
                        'pageId' => $pageId,
                    ]));
                }
                $section->setPosition($pageSectionRepository->findMaxPositionByPage($page) + 1);
            } 
            // Other sections: insert before footer if exists, else at end
            else {
                $footer = $pageSectionRepository->createQueryBuilder('ps')
                    ->where('ps.page = :page')
                    ->andWhere('ps.type = :type')
                    ->setParameter('page', $page)
                    ->setParameter('type', 'footer')
                    ->getQuery()
                    ->getOneOrNullResult();
                
                if ($footer) {
                    $section->setPosition($footer->getPosition());
                    $footer->setPosition($footer->getPosition() + 1);
                } else {
                    $section->setPosition($pageSectionRepository->findMaxPositionByPage($page) + 1);
                }
            }

            $entityManager->persist($section);
            $entityManager->flush();

            // Normalize positions to ensure sequential 1..N
            $this->normalizeSectionPositions($page, $pageSectionRepository, $entityManager);

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
            $this->normalizeSectionPositions($page, $pageSectionRepository, $entityManager);
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

        // Header and footer cannot be moved
        if ($section->getType() === 'header' || $section->getType() === 'footer') {
            $this->addFlash('error', 'This section type cannot be moved');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]));
        }

        $previousSection = $pageSectionRepository->findPreviousSection($section);
        
        if ($previousSection && $previousSection->getType() !== 'header') {
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

        // Header and footer cannot be moved
        if ($section->getType() === 'header' || $section->getType() === 'footer') {
            $this->addFlash('error', 'This section type cannot be moved');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]));
        }

        $nextSection = $pageSectionRepository->findNextSection($section);
        
        if ($nextSection && $nextSection->getType() !== 'footer') {
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

    #[Route('/section/{sectionId}/detach', name: 'app_section_detach', methods: ['GET'])]
    public function detach(int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
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

        // If the section has a reference, detach it by copying the data and removing the reference
        if ($section->getReferenceSection()) {
            $section->setData($section->getEffectiveData());
            $section->setReferenceSection(null);
            $entityManager->flush();
            $this->addFlash('success', 'Section detached from reference successfully');
        }

        return $this->redirect($this->generateUrl('app_page_builder', [
            'siteId' => $siteId,
            'pageId' => $pageId,
        ]));
    }

    /**
     * Normalizes section positions to ensure they are sequential: 1..N
     */
    private function normalizeSectionPositions(Page $page, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): void
    {
        $sections = $pageSectionRepository->findByPageOrdered($page);
        
        foreach ($sections as $index => $section) {
            $section->setPosition($index + 1);
        }
        
        $entityManager->flush();
    }
}
