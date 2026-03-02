<?php

namespace App\Command;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-sites',
    description: 'List all sites',
)]
class ListSitesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $sites = $this->em->getRepository(Site::class)->findAll();
        
        $io->table(
            ['ID', 'Name', 'Domain', 'Locale', 'Active'],
            array_map(function (Site $site) {
                return [
                    $site->getId(),
                    $site->getName(),
                    $site->getDomain(),
                    $site->getDefaultLocale(),
                    $site->isIsActive() ? 'Yes' : 'No',
                ];
            }, $sites)
        );

        return Command::SUCCESS;
    }
}
