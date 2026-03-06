<?php

// Test script to verify the form implementation
require __DIR__.'/../vendor/autoload.php';

use App\Entity\PageSection;
use App\Form\PageSectionType;
use App\Form\FormFieldType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

// Create validator
$validator = Validation::createValidatorBuilder()
    ->addMethodMapping('loadValidatorMetadata')
    ->getValidator();

// Create form factory
$formFactory = Symfony\Component\Form\Forms::createFormFactoryBuilder()
    ->addExtension(new HttpFoundationExtension())
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();

// Create a session for flash messages
$session = new Session(
    new NativeSessionStorage([], new AttributeBag(), new FlashBag())
);
$session->start();

// Create test section
$section = new PageSection();
$section->setType('hero_split');

// Build form
$form = $formFactory->create(PageSectionType::class, $section);

// Handle request
$request = Request::createFromGlobals();
$form->handleRequest($request);

if ($form->isSubmitted()) {
    echo '<h2>Form Submitted</h2>';
    echo '<h3>Is Valid? ' . ($form->isValid() ? '✅' : '❌') . '</h3>';
    
    if (!$form->isValid()) {
        echo '<h3>Errors:</h3>';
        echo '<pre>';
        print_r($form->getErrors(true));
        echo '</pre>';
    }
    
    echo '<h3>Data:</h3>';
    echo '<pre>';
    print_r($form->getData());
    echo '</pre>';
}

// Output HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-control { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .alert { padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <h1>Test Hero Split Form</h1>
    
    <div class="form-container">
        <form action="" method="post">
            <?php
            // Render form
            $formView = $form->createView();
            $renderer = new \Symfony\Component\Form\FormRenderer(
                new \Symfony\Component\Templating\EngineInterface()
            );
            
            // Render form fields manually for testing
            foreach ($formView->children as $name => $field) {
                if (in_array($name, ['_token'])) continue;
                
                echo '<div class="form-group">';
                echo '<label>' . $name . '</label>';
                echo '<div>' . $name . '_render</div>';
                echo '</div>';
            }
            ?>
            
            <button type="submit" class="btn">Submit Test Form</button>
            <input type="hidden" name="_token" value="test_token">
        </form>
    </div>
</body>
</html>
