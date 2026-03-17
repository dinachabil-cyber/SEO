<?php

namespace App\Controller;

use App\Entity\PasswordResetRequest;
use App\Entity\User;
use App\Form\AdminChangePasswordType;
use App\Repository\PasswordResetRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateTimeImmutable;

#[Route('/admin/password-reset-requests')]
#[IsGranted('ROLE_ADMIN')]
class AdminPasswordResetRequestController extends AbstractController
{
    #[Route('/', name: 'admin_password_reset_index', methods: ['GET'])]
    public function index(PasswordResetRequestRepository $repository): Response
    {
        return $this->render('admin/password_reset/index.html.twig', [
            'requests' => $repository->findAllOrdered(),
        ]);
    }

    #[Route('/{id}', name: 'admin_password_reset_show', methods: ['GET'])]
    public function show(PasswordResetRequest $request): Response
    {
        return $this->render('admin/password_reset/show.html.twig', [
            'request' => $request,
        ]);
    }

    #[Route('/{id}/approve', name: 'admin_password_reset_approve', methods: ['POST'])]
    public function approve(
        PasswordResetRequest $request,
        EntityManagerInterface $entityManager,
        Request $httpRequest
    ): Response {
        $this->validateCsrfToken($httpRequest, 'approve' . $request->getId());
        
        $request->setStatus(PasswordResetRequest::STATUS_APPROVED);
        $request->setProcessedAt(new DateTimeImmutable());
        $request->setProcessedBy($this->getUser());
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Password reset request approved. You can now set a new password for the user.');
        
        return $this->redirectToRoute('admin_password_reset_show', ['id' => $request->getId()]);
    }

    #[Route('/{id}/reject', name: 'admin_password_reset_reject', methods: ['POST'])]
    public function reject(
        PasswordResetRequest $request,
        EntityManagerInterface $entityManager,
        Request $httpRequest
    ): Response {
        $this->validateCsrfToken($httpRequest, 'reject' . $request->getId());
        
        $request->setStatus(PasswordResetRequest::STATUS_REJECTED);
        $request->setProcessedAt(new DateTimeImmutable());
        $request->setProcessedBy($this->getUser());
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Password reset request rejected.');
        
        return $this->redirectToRoute('admin_password_reset_show', ['id' => $request->getId()]);
    }

    #[Route('/{id}/set-password', name: 'admin_password_reset_set_password', methods: ['GET', 'POST'])]
    public function setPassword(
        PasswordResetRequest $request,
        Request $httpRequest,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$request->isApproved() && !$request->isPending()) {
            $this->addFlash('error', 'This request must be approved first.');
            return $this->redirectToRoute('admin_password_reset_show', ['id' => $request->getId()]);
        }
        
        $user = $request->getUser();
        
        $form = $this->createForm(AdminChangePasswordType::class);
        $form->handleRequest($httpRequest);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            
            $request->setStatus(PasswordResetRequest::STATUS_COMPLETED);
            $request->setProcessedAt(new DateTimeImmutable());
            $request->setProcessedBy($this->getUser());
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Password has been reset successfully for user ' . $user->getName());
            
            return $this->redirectToRoute('admin_password_reset_show', ['id' => $request->getId()]);
        }
        
        return $this->render('admin/password_reset/set_password.html.twig', [
            'request' => $request,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/complete', name: 'admin_password_reset_complete', methods: ['POST'])]
    public function complete(
        PasswordResetRequest $request,
        EntityManagerInterface $entityManager,
        Request $httpRequest
    ): Response {
        $this->validateCsrfToken($httpRequest, 'complete' . $request->getId());
        
        $request->setStatus(PasswordResetRequest::STATUS_COMPLETED);
        $request->setProcessedAt(new DateTimeImmutable());
        $request->setProcessedBy($this->getUser());
        
        $entityManager->flush();
        
        $this->addFlash('success', 'Password reset request marked as completed.');
        
        return $this->redirectToRoute('admin_password_reset_show', ['id' => $request->getId()]);
    }

    private function validateCsrfToken(Request $request, string $id): void
    {
        $token = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
