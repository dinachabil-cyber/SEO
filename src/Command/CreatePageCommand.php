<?php

namespace App\Command;

use App\Entity\Page;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-page',
    description: 'Create a new page',
)]
class CreatePageCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('slug', InputArgument::REQUIRED, 'Page slug')
            ->addArgument('siteId', InputArgument::REQUIRED, 'Site ID')
            ->addArgument('metaTitle', InputArgument::OPTIONAL, 'Meta title')
            ->addArgument('metaDescription', InputArgument::OPTIONAL, 'Meta description')
            ->addArgument('h1', InputArgument::OPTIONAL, 'H1 title')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $slug = $input->getArgument('slug');
        $siteId = $input->getArgument('siteId');
        $metaTitle = $input->getArgument('metaTitle');
        $metaDescription = $input->getArgument('metaDescription');
        $h1 = $input->getArgument('h1');

        $site = $this->em->getRepository(Site::class)->find($siteId);
        
        if (!$site) {
            $io->error(sprintf('Site with ID %s not found', $siteId));
            return Command::FAILURE;
        }

        $page = new Page();
        $page->setSlug($slug);
        $page->setSite($site);
        $page->setMetaTitle($metaTitle);
        $page->setMetaDescription($metaDescription);
        $page->setH1($h1);
        $page->setIsPublished(true);

        $this->em->persist($page);
        $this->em->flush();

        $io->success(sprintf('Page "%s" created for site "%s"', $slug, $site->getName()));

        return Command::SUCCESS;
    }
}
