<?php

namespace App\Command;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-site',
    description: 'Create a new site',
)]
class CreateSiteCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Site name')
            ->addArgument('domain', InputArgument::REQUIRED, 'Site domain')
            ->addArgument('locale', InputArgument::OPTIONAL, 'Default locale', 'fr')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $name = $input->getArgument('name');
        $domain = $input->getArgument('domain');
        $locale = $input->getArgument('locale');

        $site = new Site();
        $site->setName($name);
        $site->setDomain($domain);
        $site->setDefaultLocale($locale);

        $this->em->persist($site);
        $this->em->flush();

        $io->success(sprintf('Site "%s" created with domain "%s" and locale "%s"', $name, $domain, $locale));

        return Command::SUCCESS;
    }
}
