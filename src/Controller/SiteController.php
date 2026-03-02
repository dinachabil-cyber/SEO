<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class SiteController extends AbstractController
{
    #[Route('/site/create', name: 'app_admin_site_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_site_dashboard', ['id' => $site->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/site_create.html.twig', [
            'site' => $site,
            'form' => $form,
        ]);
    }

    #[Route('/site/{id}', name: 'app_admin_site_dashboard', methods: ['GET'])]
    public function dashboard(Site $site): Response
    {
        return $this->render('admin/site_dashboard.html.twig', [
            'site' => $site,
        ]);
    }
}
