<?php

namespace App\Controller;

use App\Entity\PasswordResetRequest;
use App\Repository\PasswordResetRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/password-reset')]
class PasswordResetRequestController extends AbstractController
{
    #[Route('/request', name: 'password_reset_request', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function request(): Response
    {
        return $this->render('password_reset/request.html.twig');
    }

    #[Route('/submit', name: 'password_reset_submit', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function submit(
        PasswordResetRequestRepository $repository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        
        // Check if user already has a pending request
        $existingRequest = $repository->findPendingByUser($user->getId());
        
        if ($existingRequest) {
            $this->addFlash('warning', 'You already have a pending password reset request.');
            return $this->redirectToRoute('password_reset_request');
        }
        
        $request = new PasswordResetRequest();
        $request->setUser($user);
        $request->setStatus(PasswordResetRequest::STATUS_PENDING);
        
        $entityManager->persist($request);
        $entityManager->flush();
        
        $this->addFlash('success', 'Your password reset request has been submitted. An admin will review it shortly.');
        
        return $this->redirectToRoute('app_login');
    }

    #[Route('/status', name: 'password_reset_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function status(PasswordResetRequestRepository $repository): Response
    {
        $user = $this->getUser();
        
        $requests = $repository->findBy(['user' => $user], ['requestedAt' => 'DESC']);
        
        return $this->render('password_reset/status.html.twig', [
            'requests' => $requests,
        ]);
    }
}
