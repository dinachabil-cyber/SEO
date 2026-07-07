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
#[IsGranted('ROLE_USER')]
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
        
        // Only show sites owned by the current user if not admin
        $user = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN')) {
            $filters['owner'] = $user;
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
        $site->setOwner($this->getUser());
        $site->setStatus('Draft');
        
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Debug information
            error_log('Form submitted');
            error_log('Form isValid(): ' . ($form->isValid() ? 'true' : 'false'));

            if (!$form->isValid()) {
                // Surface exact validation errors to quickly identify why site creation fails
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getOrigin()->getName() . ': ' . $error->getMessage();
                }

                $message = 'Site create failed. ' . (count($errors) ? implode(' | ', $errors) : 'Unknown validation error');
                $this->addFlash('error', $message);
                error_log($message);
            }

            if ($form->isValid()) {
                try {
                    error_log('Attempting to persist site');
                    $entityManager->persist($site);
                    error_log('Site persisted, flushing to database');
                    $entityManager->flush();
                    error_log('Site flushed successfully');

                    $this->addFlash('success', 'Site created successfully');

                    return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $this->addFlash('error', 'This domain already exists. Please use a different domain.');
                    error_log('Unique constraint violation: ' . $e->getMessage());
                    error_log('Stack trace: ' . $e->getTraceAsString());
                } catch (\Exception $e) {
                    $this->addFlash('error', 'An error occurred while creating the site. Please try again later. Error: ' . $e->getMessage());
                    error_log('Error creating site: ' . $e->getMessage());
                    error_log('Stack trace: ' . $e->getTraceAsString());
                }
            }
        }

        return $this->render('admin/site/new.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_site_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, SiteRepository $siteRepository): Response
    {
        $site = $siteRepository->findWithPagesAndSections($id);
        
        if (!$site) {
            $this->addFlash('error', 'Site not found');
            return $this->redirectToRoute('app_site_index');
        }

        // Check if user has access to this site
        if (!$this->isGranted('ROLE_ADMIN') && $site->getOwner() !== $this->getUser()) {
            $this->addFlash('error', 'Access denied');
            return $this->redirectToRoute('app_site_index');
        }

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

    #[Route('/{id}/edit', name: 'app_site_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        // Check if user has access to this site
        if (!$this->isGranted('ROLE_ADMIN') && $site->getOwner() !== $this->getUser()) {
            $this->addFlash('error', 'Access denied');
            return $this->redirectToRoute('app_site_index');
        }

        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();

                $this->addFlash('success', 'Site updated successfully');

                return $this->redirectToRoute('app_site_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'This domain already exists. Please use a different domain.');
                error_log('Unique constraint violation: ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred while updating the site. Please try again later.');
                error_log('Error updating site: ' . $e->getMessage());
            }
        }

        return $this->render('admin/site/edit.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_site_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        // Check if user has access to this site
        if (!$this->isGranted('ROLE_ADMIN') && $site->getOwner() !== $this->getUser()) {
            $this->addFlash('error', 'Access denied');
            return $this->redirectToRoute('app_site_index');
        }

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
