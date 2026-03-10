<?php

namespace App\Controller;

use App\Entity\ReferenceSection;
use App\Form\PageSectionType;
use App\Repository\ReferenceSectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/reference')]
class ReferenceSectionController extends AbstractController
{
    #[Route('/', name: 'app_reference_section_index', methods: ['GET'])]
    public function index(ReferenceSectionRepository $referenceSectionRepository): Response
    {
        return $this->render('admin/reference/index.html.twig', [
            'reference_sections' => $referenceSectionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reference_section_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $referenceSection = new ReferenceSection();
        $form = $this->createForm(PageSectionType::class, $referenceSection, [
            'data_class' => ReferenceSection::class,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($referenceSection);
            $entityManager->flush();

            return $this->redirectToRoute('app_reference_section_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reference/new.html.twig', [
            'reference_section' => $referenceSection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reference_section_show', methods: ['GET'])]
    public function show(ReferenceSection $referenceSection): Response
    {
        return $this->render('admin/reference/show.html.twig', [
            'reference_section' => $referenceSection,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reference_section_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ReferenceSection $referenceSection, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PageSectionType::class, $referenceSection, [
            'data_class' => ReferenceSection::class,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reference_section_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/reference/edit.html.twig', [
            'reference_section' => $referenceSection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reference_section_delete', methods: ['POST'])]
    public function delete(Request $request, ReferenceSection $referenceSection, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$referenceSection->getId(), $request->request->get('_token'))) {
            $entityManager->remove($referenceSection);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reference_section_index', [], Response::HTTP_SEE_OTHER);
    }
}
