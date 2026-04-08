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

#[Route('/')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function dashboard(Request $request, SiteRepository $siteRepository): Response
    {
        $filtersForm = $this->createForm(SiteFiltersType::class);
        $filtersForm->handleRequest($request);
        
        $filters = [];
        if ($filtersForm->isSubmitted() && $filtersForm->isValid()) {
            $filters = array_filter($filtersForm->getData());
        }
        
        // Only show user's own sites
        $filters['owner'] = $this->getUser();
        $sites = $siteRepository->findFiltered($filters);

        return $this->render('dashboard/index.html.twig', [
            'sites' => $sites,
            'filtersForm' => $filtersForm,
        ]);
    }

    #[Route('/sites', name: 'app_user_site_index', methods: ['GET'])]
    public function index(Request $request, SiteRepository $siteRepository): Response
    {
        $filtersForm = $this->createForm(SiteFiltersType::class);
        $filtersForm->handleRequest($request);
        
        $filters = [];
        if ($filtersForm->isSubmitted() && $filtersForm->isValid()) {
            $filters = array_filter($filtersForm->getData());
        }
        
        // Only show user's own sites
        $filters['owner'] = $this->getUser();
        $sites = $siteRepository->findFiltered($filters);

        return $this->render('dashboard/sites/index.html.twig', [
            'sites' => $sites,
            'filtersForm' => $filtersForm,
        ]);
    }

    #[Route('/sites/new', name: 'app_user_site_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $site->setOwner($this->getUser());
        $site->setStatus('Draft');
        
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            $this->addFlash('success', 'Site created successfully');

            return $this->redirectToRoute('app_user_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/sites/new.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/sites/{id}', name: 'app_user_site_show', methods: ['GET'])]
    public function show(int $id, SiteRepository $siteRepository): Response
    {
        $site = $siteRepository->findWithPagesAndSections($id);
        
        if (!$site) {
            $this->addFlash('error', 'Site not found');
            return $this->redirectToRoute('app_user_site_index');
        }

        // Check if user has access
        if (!$this->isGranted('VIEW', $site)) {
            $this->addFlash('error', 'You do not have access to this site');
            return $this->redirectToRoute('app_user_site_index');
        }

        $totalPages = count($site->getPages());
        $publishedPages = count(array_filter($site->getPages()->toArray(), function ($page) {
            return $page->isIsPublished();
        }));
        $draftPages = $totalPages - $publishedPages;

        return $this->render('dashboard/sites/show.html.twig', [
            'site' => $site,
            'totalPages' => $totalPages,
            'publishedPages' => $publishedPages,
            'draftPages' => $draftPages,
        ]);
    }

    #[Route('/sites/{id}/edit', name: 'app_user_site_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        // Check if user has access
        if (!$this->isGranted('EDIT', $site)) {
            $this->addFlash('error', 'You do not have access to edit this site');
            return $this->redirectToRoute('app_user_site_index');
        }

        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Site updated successfully');

            return $this->redirectToRoute('app_user_site_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('dashboard/sites/edit.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/sites/{id}/delete', name: 'app_user_site_delete', methods: ['POST'])]
    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        // Check if user has access
        if (!$this->isGranted('DELETE', $site)) {
            $this->addFlash('error', 'You do not have access to delete this site');
            return $this->redirectToRoute('app_user_site_index');
        }

        if ($this->isCsrfTokenValid('delete' . $site->getId(), $request->request->get('_token'))) {
            $entityManager->remove($site);
            $entityManager->flush();

            $this->addFlash('success', 'Site deleted successfully');
        } else {
            $this->addFlash('error', 'Invalid CSRF token');
        }

        return $this->redirectToRoute('app_user_site_index', [], Response::HTTP_SEE_OTHER);
    }
}
