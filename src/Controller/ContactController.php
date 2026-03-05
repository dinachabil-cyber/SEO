<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact', methods: ['POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        // Handle contact form submission here
        // You can add validation, email sending, and success handling

        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $phone = $request->request->get('phone');
        $message = $request->request->get('message');

        // Example: Send an email (configure mailer in .env file)
        /*
        $email = (new Email())
            ->from($email)
            ->to('your@email.com')
            ->subject('New Contact Form Submission')
            ->text("Name: $name\nEmail: $email\nPhone: $phone\nMessage: $message");

        $mailer->send($email);
        */

        // For now, redirect back with a success message
        $this->addFlash('success', 'Your message has been sent successfully!');

        // Redirect to the previous page or homepage
        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_home'));
    }
}
