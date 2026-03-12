<?php
// This is a simple test script to check the rendering of the page
require __DIR__.'/vendor/autoload.php';

use App\Entity\Page;
use App\Repository\PageRepository;
use Doctrine\ORM\EntityManagerInterface;

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create a container (this is a simplified version)
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

echo "Page found: " . $page->getTitle() . "\n";
echo "Sections count: " . count($page->getSections()) . "\n";

// Render the page
echo "\n--- Rendering sections ---\n";
foreach ($page->getSections() as $section) {
    echo "\nSection #" . $section->getId() . ": " . $section->getType() . "\n";
    echo "Position: " . $section->getPosition() . "\n";
    echo "Data: " . json_encode($section->getData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}

// Try to render a specific section
echo "\n--- Rendering first card section ---\n";
foreach ($page->getSections() as $section) {
    if ($section->getType() === 'cards_premium') {
        $data = $section->getData();
        var_dump($data);
        break;
    }
}

// Check assets
echo "\n--- Checking assets ---\n";
$assetsPath = __DIR__.'/assets/styles';
echo "assets/styles path exists: " . (is_dir($assetsPath) ? "yes" : "no") . "\n";
$cardsPremiumCss = $assetsPath . '/components/cards-premium.css';
echo "cards-premium.css exists: " . (file_exists($cardsPremiumCss) ? "yes" : "no") . "\n";
if (file_exists($cardsPremiumCss)) {
    echo "cards-premium.css content: " . file_get_contents($cardsPremiumCss) . "\n";
}
