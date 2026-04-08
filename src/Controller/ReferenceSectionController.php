<?php

namespace App\Controller;

use App\Entity\PageSection;
use App\Entity\ReferenceSection;
use App\Form\SaveReferenceType;
use App\Repository\PageSectionRepository;
use App\Repository\ReferenceSectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reference', name: 'app_reference_')]
#[IsGranted('ROLE_ADMIN')]
class ReferenceSectionController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ReferenceSectionRepository $repository): Response
    {
        return $this->render('admin/reference/index.html.twig', [
            'references' => $repository->findAll(),
        ]);
    }

    #[Route('/save/{siteId}/{pageId}/{sectionId}', name: 'save', methods: ['GET', 'POST'])]
    public function save(
        Request $request,
        int $siteId,
        int $pageId,
        int $sectionId,
        PageSectionRepository $pageSectionRepository,
        ReferenceSectionRepository $referenceSectionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $section = $pageSectionRepository->find($sectionId);
        
        if (!$section || $section->getPage()->getId() !== $pageId) {
            $this->addFlash('error', 'Section not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]);
        }

        $form = $this->createForm(SaveReferenceType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();
            
            $reference = new ReferenceSection();
            $reference->setName($name);
            $reference->setType($section->getType());
            $reference->setData($section->getData());
            
            $entityManager->persist($reference);
            $entityManager->flush();

            $this->addFlash('success', 'Section saved as reference: ' . $name);
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]);
        }

        return $this->render('admin/section/save_reference.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
            'siteId' => $siteId,
            'pageId' => $pageId,
        ]);
    }

    #[Route('/import/{siteId}/{pageId}/{referenceId}', name: 'import', methods: ['GET', 'POST'])]
    public function import(
        Request $request,
        int $siteId,
        int $pageId,
        int $referenceId,
        ReferenceSectionRepository $referenceSectionRepository,
        PageSectionRepository $pageSectionRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $reference = $referenceSectionRepository->find($referenceId);
        
        if (!$reference) {
            $this->addFlash('error', 'Reference not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]);
        }

        $page = $pageSectionRepository->find($pageId)?->getPage();
        
        if (!$page || $page->getSite()->getId() !== $siteId) {
            $this->addFlash('error', 'Page not found');
            return $this->redirectToRoute('app_page_builder', [
                'siteId' => $siteId,
                'pageId' => $pageId,
            ]);
        }

        $section = new PageSection();
        $section->setPage($page);
        $section->setType($reference->getType());
        $section->setData($reference->getData());
        $section->setPosition($pageSectionRepository->findMaxPositionByPage($page) + 1);
        $section->setReferenceSection($reference);
        
        $entityManager->persist($section);
        $entityManager->flush();

        $this->addFlash('success', 'Reference imported successfully');
        return $this->redirectToRoute('app_page_builder', [
            'siteId' => $siteId,
            'pageId' => $pageId,
        ]);
    }

    #[Route('/list/{siteId}/{pageId}', name: 'list', methods: ['GET'])]
    public function list(
        int $siteId,
        int $pageId,
        ReferenceSectionRepository $repository
    ): Response {
        return $this->render('admin/page/insert_reference.html.twig', [
            'references' => $repository->findAll(),
            'siteId' => $siteId,
            'pageId' => $pageId,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ReferenceSection $reference,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $reference->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reference);
            $entityManager->flush();
            $this->addFlash('success', 'Reference deleted successfully');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_reference_index');
    }
}
