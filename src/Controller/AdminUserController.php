<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminChangePasswordType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class AdminUserController extends AbstractController
{
    #[Route('/', name: 'admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder($user)
            ->add('name')
            ->add('roles')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'User updated successfully.');

            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/change-password', name: 'admin_user_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(AdminChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('newPassword')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Password changed successfully for user ' . $user->getName());
            
            return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/user/change_password.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/enable', name: 'admin_user_enable', methods: ['POST'])]
    public function enable(User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->validateCsrfToken($request, 'enable' . $user->getId());
        
        $user->setEnabled(true);
        $entityManager->flush();
        
        $this->addFlash('success', 'User ' . $user->getName() . ' has been enabled.');

        return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
    }

    #[Route('/{id}/disable', name: 'admin_user_disable', methods: ['POST'])]
    public function disable(User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->validateCsrfToken($request, 'disable' . $user->getId());
        
        $user->setEnabled(false);
        $entityManager->flush();
        
        $this->addFlash('success', 'User ' . $user->getName() . ' has been disabled.');

        return $this->redirectToRoute('admin_user_show', ['id' => $user->getId()]);
    }

    private function validateCsrfToken(Request $request, string $id): void
    {
        $token = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid($id, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }
    }
}
