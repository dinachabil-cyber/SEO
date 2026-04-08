<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestPerformanceController extends AbstractController
{
    #[Route('/test-performance', name: 'app_test_performance')]
    public function index(): Response
    {
        return new Response('Hello World');
    }
}
