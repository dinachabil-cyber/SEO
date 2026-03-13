<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Form\SiteFiltersType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/site')]
#[IsGranted('ROLE_ADMIN')]
class SiteController extends AbstractController
{
    #[Route('/', name: 'app_site_index', methods: ['GET'])]
    public function index(Request $request, SiteRepository $siteRepository): Response
    {
        $filtersForm = $this->createForm(SiteFiltersType::class);
        $filtersForm->handleRequest($request);
        
        $filters = [];
        if ($filtersForm->isSubmitted() && $filtersForm->isValid()) {
            $filters = array_filter($filtersForm->getData());
        }
        
        $sites = $siteRepository->findFiltered($filters);

        return $this->render('admin/site/index.html.twig', [
            'sites' => $sites,
            'filtersForm' => $filtersForm,
        ]);
    }

    #[Route('/new', name: 'app_site_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $site->setUser($this->getUser());
        $site->setStatus('Draft');
        
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            $this->addFlash('success', 'Site created successfully');

            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/site/new.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_show', methods: ['GET'])]
    public function show(Site $site): Response
    {
        $totalPages = count($site->getPages());
        $publishedPages = count(array_filter($site->getPages()->toArray(), function ($page) {
            return $page->isIsPublished();
        }));
        $draftPages = $totalPages - $publishedPages;

        return $this->render('admin/site/show.html.twig', [
            'site' => $site,
            'totalPages' => $totalPages,
            'publishedPages' => $publishedPages,
            'draftPages' => $draftPages,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Site updated successfully');

            return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/site/edit.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_site_delete', methods: ['POST'])]
    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $site->getId(), $request->request->get('_token'))) {
            $entityManager->remove($site);
            $entityManager->flush();

            $this->addFlash('success', 'Site deleted successfully');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
    }
}
