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
use Psr\Log\LoggerInterface;

#[Route('/admin/site/{siteId}/page/{pageId}', requirements: ['siteId' => '\d+', 'pageId' => '\d+'])]
class PageBuilderController extends AbstractController
{
    use FormErrorFlashTrait;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    #[Route('/builder', name: 'app_page_builder', methods: ['GET'])]
    public function index(int $siteId, int $pageId, PageRepository $pageRepository): Response
    {
        $page = $pageRepository->findWithSections($pageId);

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
        $page = $this->findOwnedPage($siteId, $pageId, $pageRepository);
        if (!$page) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = new PageSection();
        $section->setPage($page);
        $section->setPosition($pageSectionRepository->findMaxPositionByPage($page) + 1);

        $form = $this->createForm(PageSectionType::class, $section);
        $form->handleRequest($request);

        // When the type is changed in the "Add Section" form, only re-render with the
        // type-specific fields. Do not persist a (possibly empty) section yet.
        if ($form->isSubmitted() && $request->request->has('_rerender')) {
            return $this->render('admin/section/new.html.twig', [
                'section' => $section,
                'page' => $page,
                'site' => $page->getSite(),
                'form' => $form,
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($section);
            $entityManager->flush();

            $this->addFlash('success', 'Section added successfully');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/section/new.html.twig', [
            'section' => $section,
            'page' => $page,
            'site' => $page->getSite(),
            'form' => $form,
        ]);
    }

    #[Route('/section/{sectionId}/edit', name: 'app_section_edit', methods: ['GET', 'POST'], requirements: ['sectionId' => '\d+'])]
    public function edit(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $this->findOwnedPage($siteId, $pageId, $pageRepository);
        if (!$page) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = $this->findOwnedSection($pageId, $sectionId, $pageSectionRepository);
        if (!$section) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(PageSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($section->getReferenceSection()) {
                $section->setReferenceSection(null);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Section updated successfully');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        // Debug helper for 422 cases: surface form errors to the UI
        if ($form->isSubmitted() && !$form->isValid()) {
            $submitted = $form->getSubmittedData();

            $this->flashFormErrors($form, 'Section update failed. Check your inputs and try again.');

            $this->logger->info('Section edit invalid', [
                'submitted_type' => is_array($submitted) ? array_keys($submitted) : null,
                'submitted_data' => $submitted,
            ]);
        }

        return $this->render('admin/section/edit.html.twig', [
            'section' => $section,
            'page' => $page,
            'site' => $page->getSite(),
            'form' => $form,
        ]);
    }

    #[Route('/section/{sectionId}/delete', name: 'app_section_delete', methods: ['POST'], requirements: ['sectionId' => '\d+'])]
    public function delete(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        $page = $this->findOwnedPage($siteId, $pageId, $pageRepository);
        if (!$page) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = $this->findOwnedSection($pageId, $sectionId, $pageSectionRepository);
        if (!$section) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete_section' . $section->getId(), $request->request->get('_token'))) {
            $entityManager->remove($section);
            $entityManager->flush();
            $this->addFlash('success', 'Section deleted successfully');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_page_builder', [
            'siteId' => $siteId,
            'pageId' => $pageId,
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/section/{sectionId}/up', name: 'app_section_up', methods: ['POST'], requirements: ['sectionId' => '\d+'])]
    public function up(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        return $this->moveSection($request, $siteId, $pageId, $sectionId, 'up', $pageRepository, $pageSectionRepository, $entityManager);
    }

    #[Route('/section/{sectionId}/down', name: 'app_section_down', methods: ['POST'], requirements: ['sectionId' => '\d+'])]
    public function down(Request $request, int $siteId, int $pageId, int $sectionId, PageRepository $pageRepository, PageSectionRepository $pageSectionRepository, EntityManagerInterface $entityManager): Response
    {
        return $this->moveSection($request, $siteId, $pageId, $sectionId, 'down', $pageRepository, $pageSectionRepository, $entityManager);
    }

    private function moveSection(
        Request $request,
        int $siteId,
        int $pageId,
        int $sectionId,
        string $direction,
        PageRepository $pageRepository,
        PageSectionRepository $pageSectionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $page = $this->findOwnedPage($siteId, $pageId, $pageRepository);
        if (!$page) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_site_show', ['id' => $siteId], Response::HTTP_SEE_OTHER);
        }

        $section = $this->findOwnedSection($pageId, $sectionId, $pageSectionRepository);
        if (!$section) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        $neighbor = $direction === 'up'
            ? $pageSectionRepository->findPreviousSection($section)
            : $pageSectionRepository->findNextSection($section);

        if (!$neighbor) {
            $this->logger->info(sprintf('Section %s - already at %s', $direction, $direction === 'up' ? 'top' : 'bottom'));
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        if (!$this->isCsrfTokenValid('move_section' . $section->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token');
            $this->logger->warning('Section move - CSRF validation failed', [
                'sectionId' => $section->getId(),
                'direction' => $direction,
            ]);
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ], Response::HTTP_SEE_OTHER);
        }

        // Normalize positions, then swap with the neighbor
        $sections = $pageSectionRepository->findByPageOrdered($page);
        foreach ($sections as $index => $s) {
            $s->setPosition($index);
        }

        $currentPosition = $section->getPosition();
        $section->setPosition($neighbor->getPosition());
        $neighbor->setPosition($currentPosition);

        $entityManager->flush();
        $this->addFlash('success', sprintf('Section moved %s successfully', $direction));
        $this->logger->info(sprintf('Section %s - position swapped', $direction), [
            'sectionId' => $section->getId(),
            'sectionNewPosition' => $section->getPosition(),
            'neighborId' => $neighbor->getId(),
            'neighborNewPosition' => $neighbor->getPosition(),
        ]);

        return $this->redirectToRoute('app_page_builder', [
            'siteId' => $siteId,
            'pageId' => $pageId,
        ], Response::HTTP_SEE_OTHER);
    }

    private function findOwnedPage(int $siteId, int $pageId, PageRepository $pageRepository): ?Page
    {
        $page = $pageRepository->find($pageId);
        return ($page && $page->getSite()->getId() === $siteId) ? $page : null;
    }

    private function findOwnedSection(int $pageId, int $sectionId, PageSectionRepository $pageSectionRepository): ?PageSection
    {
        $section = $pageSectionRepository->find($sectionId);
        return ($section && $section->getPage()->getId() === $pageId) ? $section : null;
    }
}
