<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Site;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/event')]
#[IsGranted('ROLE_USER')]
class EventController extends AbstractController
{
    #[Route('/', name: 'admin_event_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository, SiteRepository $siteRepository): Response
    {
        $user = $this->getUser();
        
        if ($this->isGranted('ROLE_ADMIN')) {
            $events = $eventRepository->findAll();
            $sites = $siteRepository->findAll();
        } else {
            $sites = $siteRepository->findBy(['owner' => $user]);
            $siteIds = array_map(fn($s) => $s->getId(), $sites);
            $events = $siteIds 
                ? $eventRepository->createQueryBuilder('e')
                    ->where('e.site IN (:siteIds)')
                    ->setParameter('siteIds', $siteIds)
                    ->getQuery()
                    ->getResult()
                : [];
        }

        $selectedSiteId = $request->query->get('site');
        if ($selectedSiteId) {
            $events = array_filter($events, fn($e) => $e->getSite()?->getId() == $selectedSiteId);
        }

        return $this->render('admin/event/index.html.twig', [
            'events' => $events,
            'sites' => $sites,
            'selectedSiteId' => $selectedSiteId,
        ]);
    }

    #[Route('/new', name: 'admin_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SiteRepository $siteRepository): Response
    {
        $event = new Event();
        
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $sites = $siteRepository->findAll();
        } else {
            $sites = $siteRepository->findBy(['owner' => $user]);
        }

        $form = $this->createForm(EventType::class, $event, [
            'sites' => $sites,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created successfully');

            return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, SiteRepository $siteRepository): Response
    {
        $user = $this->getUser();
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $site = $event->getSite();
            if (!$site || $site->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not have access to this event');
            }
        }

        $form = $this->createForm(EventType::class, $event, [
            'sites' => $this->isGranted('ROLE_ADMIN') 
                ? $siteRepository->findAll() 
                : $siteRepository->findBy(['owner' => $user]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Event updated successfully');

            return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/duplicate', name: 'admin_event_duplicate', methods: ['GET'])]
    public function duplicate(Event $event, EntityManagerInterface $entityManager, SiteRepository $siteRepository): Response
    {
        $user = $this->getUser();
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $site = $event->getSite();
            if (!$site || $site->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not have access to this event');
            }
        }

        $newEvent = new Event();
        $newEvent->setTitle($event->getTitle() . ' (Copy)');
        $newEvent->setDescription($event->getDescription());
        $newEvent->setStartAt($event->getStartAt());
        $newEvent->setEndAt($event->getEndAt());
        $newEvent->setLocation($event->getLocation());
        $newEvent->setSlug($event->getSlug() . '-copy-' . time());
        $newEvent->setSite($event->getSite());

        $entityManager->persist($newEvent);
        $entityManager->flush();

        $this->addFlash('success', 'Event duplicated successfully');

        return $this->redirectToRoute('admin_event_edit', ['id' => $newEvent->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'admin_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$this->isGranted('ROLE_ADMIN')) {
            $site = $event->getSite();
            if (!$site || $site->getOwner() !== $user) {
                throw $this->createAccessDeniedException('You do not have access to this event');
            }
        }

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('success', 'Event deleted successfully');
        }

        return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
    }
}