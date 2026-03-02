<?php

namespace App\Controller;

use App\Entity\Page;
use App\Entity\PageSection;
use App\Entity\Site;
use App\Form\PageType;
use App\Form\PageSectionType;
use App\Repository\PageRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class PageController extends AbstractController
{
    #[Route('/site/{id}/page/create', name: 'app_admin_page_create', methods: ['GET', 'POST'])]
    public function create(Site $site, Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = new Page();
        $page->setSite($site);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($page);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_page_builder', ['pageId' => $page->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/page_create.html.twig', [
            'site' => $site,
            'page' => $page,
            'form' => $form,
        ]);
    }

    #[Route('/page/{pageId}/builder', name: 'app_admin_page_builder', methods: ['GET', 'POST'])]
    public function builder(int $pageId, PageRepository $pageRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = $pageRepository->find($pageId);
        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }

        $section = new PageSection();
        $section->setPage($page);
        $form = $this->createForm(PageSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle section data based on type
            $type = $section->getType();
            $data = [];
            
            switch ($type) {
                case 'header':
                    $data = [
                        'logoText' => $request->request->get('header_logo_text', 'Logo'),
                        'menu' => json_decode($request->request->get('header_menu', '[]'), true),
                    ];
                    break;
                case 'body':
                    $data = [
                        'html' => $request->request->get('body_html', ''),
                    ];
                    break;
                case 'image':
                    $data = [
                        'src' => $request->request->get('image_src', ''),
                        'alt' => $request->request->get('image_alt', ''),
                    ];
                    break;
                case 'cards':
                    $data = [
                        'cards' => json_decode($request->request->get('cards_data', '[]'), true),
                    ];
                    break;
                case 'form':
                    $data = [
                        'title' => $request->request->get('form_title', ''),
                        'fields' => json_decode($request->request->get('form_fields', '[]'), true),
                        'submitText' => $request->request->get('form_submit_text', 'Submit'),
                    ];
                    break;
                case 'footer':
                    $data = [
                        'text' => $request->request->get('footer_text', ''),
                        'links' => json_decode($request->request->get('footer_links', '[]'), true),
                    ];
                    break;
            }
            
            $section->setData($data);
            $entityManager->persist($section);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_page_builder', ['pageId' => $pageId], Response::HTTP_SEE_OTHER);
        }

        // Delete section
        if ($request->request->has('delete_section')) {
            $sectionId = $request->request->get('delete_section');
            $section = $entityManager->getRepository(PageSection::class)->find($sectionId);
            if ($section && $section->getPage() === $page) {
                $entityManager->remove($section);
                $entityManager->flush();
            }
            return $this->redirectToRoute('app_admin_page_builder', ['pageId' => $pageId], Response::HTTP_SEE_OTHER);
        }

        $sections = $entityManager->getRepository(PageSection::class)->findBy(['page' => $page], ['position' => 'ASC']);

        return $this->render('admin/page_builder.html.twig', [
            'page' => $page,
            'form' => $form,
            'sections' => $sections,
        ]);
    }
}
