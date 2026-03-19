<?php

namespace App\Controller;

use App\Entity\Page;
use App\Repository\PageRepository;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/')]
class FrontController extends AbstractController
{
    #[Route('/{slug}', name: 'app_front_page', methods: ['GET'])]
    public function index(string $slug, PageRepository $pageRepository, SiteRepository $siteRepository, Request $request, LoggerInterface $logger): Response
    {
        // DEBUG: Log Turbo-related headers
        $turboFrame = $request->headers->get('Turbo-Frame');
        $turboStream = $request->headers->get('Accept');
        $logger->info('FrontController: Request headers', [
            'slug' => $slug,
            'turbo_frame' => $turboFrame,
            'accept' => $turboStream,
            'user_agent' => $request->headers->get('User-Agent'),
        ]);
        
        // DEBUG: Log the incoming request
        $logger->info('FrontController: Request for slug', ['slug' => $slug]);
        
        // Get active site (in a real scenario, you'd use domain detection)
        $site = $siteRepository->findOneBy(['isActive' => true]);
        
        $logger->info('FrontController: Active site found', [
            'site' => $site ? $site->getDomain() : 'null',
            'site_id' => $site ? $site->getId() : null
        ]);
        
        if (!$site) {
            throw $this->createNotFoundException('No active site found');
        }

        // DEBUG: Check if any pages exist for this site
        $allPages = $pageRepository->findBy(['site' => $site]);
        $logger->info('FrontController: Pages for site', [
            'total_pages' => count($allPages),
            'published_pages' => count(array_filter($allPages, fn($p) => $p->isIsPublished()))
        ]);

        $page = $pageRepository->findPublishedBySiteAndSlug($slug, $site);
        
        // DEBUG: Log the query result
        $logger->info('FrontController: Page lookup result', [
            'slug' => $slug,
            'site_id' => $site->getId(),
            'found' => $page ? 'yes' : 'no'
        ]);
        
        if (!$page) {
            // DEBUG: List available slugs for debugging
            $availableSlugs = array_map(fn($p) => $p->getSlug(), $allPages);
            $logger->warning('FrontController: Page not found', [
                'requested_slug' => $slug,
                'available_slugs' => $availableSlugs
            ]);
            throw $this->createNotFoundException('Page not found');
        }

        return $this->render('front/page.html.twig', [
            'page' => $page,
            'site' => $site,
        ]);
    }

    #[Route('/contact', name: 'app_contact', methods: ['POST'])]
    public function contact(Request $request, CsrfTokenManagerInterface $csrfTokenManager, LoggerInterface $logger): RedirectResponse
    {
        // Validate CSRF token
        $csrfToken = $request->request->get('_csrf_token');
        if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('contact_form', $csrfToken))) {
            $logger->warning('FrontController: Invalid CSRF token');
            $this->addFlash('error', 'Invalid form submission. Please try again.');
            return $this->redirectToRoute('app_home');
        }
        
        // Handle the form submission here
        // For example, you could send an email, save to database, etc.
        
        // Get form data
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');
        $message = $request->request->get('message');
        
        $logger->info('Contact form submitted', [
            'name' => $name,
            'email' => $email,
            'phone' => $phone
        ]);
        
        // Add your form handling logic here
        // Example: Send email, save to database, etc.
        
        // Turbo requires form submissions to redirect
        // Redirect back to the same page or to a thank you page
        $referer = $request->headers->get('referer');
        if ($referer) {
            $this->addFlash('success', 'Thank you for your message! We will get back to you soon.');
            return $this->redirect($referer);
        }
        
        // Fallback redirect to home page
        return $this->redirectToRoute('app_home');
    }
}
