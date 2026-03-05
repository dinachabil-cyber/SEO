<?php

namespace App\Twig;

use App\Entity\Page;
use App\Entity\Site;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SiteUrlExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('production_url', [$this, 'generateProductionUrl']),
            new TwigFunction('local_preview_url', [$this, 'generateLocalPreviewUrl']),
        ];
    }

    /**
     * Generate production URL for a page (display-only)
     */
    public function generateProductionUrl(Page $page): string
    {
        $site = $page->getSite();
        
        if (!$site || !$site->getDomain()) {
            return '';
        }

        return sprintf('https://%s/%s', $site->getDomain(), $page->getSlug());
    }

    /**
     * Generate local preview URL for a page
     */
    public function generateLocalPreviewUrl(Page $page): string
    {
        return $this->generateUrlForSlug($page->getSlug());
    }

    /**
     * Generate local preview URL for a specific slug
     */
    public function generateUrlForSlug(string $slug): string
    {
        return sprintf('https://seo-project.ddev.site/%s', $slug);
    }
}
