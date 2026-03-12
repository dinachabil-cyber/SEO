<?php
// This is a simple test script to check if the premium cards section is being rendered
require __DIR__.'/vendor/autoload.php';

use App\Entity\Page;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;

// Create a container
$kernel = new \App\Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

// Get entity manager and page repository
/** @var EntityManagerInterface $entityManager */
$entityManager = $container->get(EntityManagerInterface::class);

/** @var PageRepository $pageRepository */
$pageRepository = $container->get(PageRepository::class);

// Find the page
$pageId = 4;
$page = $pageRepository->find($pageId);

if (!$page) {
    die("Page $pageId not found");
}

echo "Page: " . $page->getTitle() . "\n";
echo "Sections: " . count($page->getSections()) . "\n";

// Check if we have a cards_premium section
$hasCardsPremium = false;
foreach ($page->getSections() as $section) {
    echo "- Section " . $section->getId() . ": " . $section->getType() . "\n";
    if ($section->getType() === 'cards_premium') {
        $hasCardsPremium = true;
        echo "  DATA:\n" . var_export($section->getData(), true) . "\n";
    }
}

echo "\nHas cards_premium section: " . ($hasCardsPremium ? "YES" : "NO") . "\n";

// Check template file exists
$templatePath = __DIR__.'/templates/front/sections/_cards_premium.html.twig';
echo "Template exists: " . (file_exists($templatePath) ? "YES" : "NO") . "\n";

// Check CSS file exists
$cssPath = __DIR__.'/assets/styles/components/cards-premium.css';
echo "CSS file exists: " . (file_exists($cssPath) ? "YES" : "NO") . "\n";

// Check app.css includes cards-premium
$appCssPath = __DIR__.'/assets/styles/app.css';
if (file_exists($appCssPath)) {
    $appCssContent = file_get_contents($appCssPath);
    $hasImport = str_contains($appCssContent, 'cards-premium');
    echo "app.css includes cards-premium.css: " . ($hasImport ? "YES" : "NO") . "\n";
}

// Render the page template to see what happens
echo "\n--- Rendering front page template ---\n";
$twig = $container->get('twig');
$html = $twig->render('front/page.html.twig', [
    'page' => $page,
    'site' => $page->getSite(),
    'sections' => $page->getSections(),
]);

// Check if cards_premium is in the rendered HTML
$hasRenderedPremium = str_contains($html, 'cards_premium');
echo "Rendered HTML has cards_premium section: " . ($hasRenderedPremium ? "YES" : "NO") . "\n";

// Check if premium card classes are in the rendered HTML
$hasPremiumClasses = str_contains($html, 'premium-card');
echo "Rendered HTML has premium card classes: " . ($hasPremiumClasses ? "YES" : "NO") . "\n";

// Save rendered HTML to a file for debugging
$debugFile = __DIR__.'/debug-rendered-page.html';
file_put_contents($debugFile, $html);
echo "Rendered HTML saved to: " . $debugFile . "\n";

echo "\n--- First 2000 characters of rendered HTML ---\n";
echo substr($html, 0, 2000);
