<?php
require "vendor/autoload.php";

// Set environment variables
$_SERVER["APP_ENV"] = "dev";
$_SERVER["APP_DEBUG"] = true;

// Get the entity manager
$kernel = new App\Kernel($_SERVER["APP_ENV"], (bool)$_SERVER["APP_DEBUG"]);
$kernel->boot();
$entityManager = $kernel->getContainer()->get("doctrine.orm.entity_manager");

// Check if page exists
$page = $entityManager->getRepository(App\Entity\Page::class)->findOneBy(["slug" => "uuuuuu"]);

if ($page) {
    echo "Page found! ID: " . $page->getId() . "\n";
    echo "Title: " . $page->getMetaTitle() . "\n";
    echo "Published: " . ($page->isPublished() ? "Yes" : "No") . "\n";
    echo "Site ID: " . $page->getSite()->getId() . "\n";
} else {
    echo "Page not found!";
}

// List all pages
$pages = $entityManager->getRepository(App\Entity\Page::class)->findAll();
echo "\nAll pages in database:\n";
foreach ($pages as $page) {
    echo "- ID: " . $page->getId() . ", Slug: " . $page->getSlug() . ", Published: " . ($page->isPublished() ? "Yes" : "No") . ", Site ID: " . $page->getSite()->getId() . "\n";
}
