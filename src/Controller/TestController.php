<?php

namespace App\Controller;

use App\Entity\PageSection;
use App\Form\PageSectionType;
use App\Form\FormFieldType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test-form', name: 'app_test_form')]
    public function testForm(Request $request): Response
    {
        $section = new PageSection();
        $section->setType('hero_split');
        $form = $this->createForm(PageSectionType::class, $section, [
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd('Form submitted and valid!', $form->getData());
        }

        return $this->render('test/form.html.twig', [
            'form' => $form->createView(),
            'section' => $section,
        ]);
    }

    #[Route('/', name: 'app_test_index')]
    public function index(): Response
    {
        return $this->render('test/index.html.twig');
    }

    #[Route('/test-form-field', name: 'app_test_form_field')]
    public function testFormField(Request $request): Response
    {
        $field = [
            'label' => 'Test Field',
            'name' => 'test_field',
            'type' => 'text',
            'required' => false,
            'placeholder' => 'Enter some text',
            'width' => 'full',
            'options' => '',
        ];

        $form = $this->createForm(FormFieldType::class, $field, [
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd('Form field submitted and valid!', $form->getData());
        }

        return $this->render('test/form_field.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
