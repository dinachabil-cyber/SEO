<?php

namespace App\Command;

use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateTestSiteCommand extends Command
{
    protected static $defaultName = 'app:create-test-site';
    protected static $defaultDescription = 'Creates a test site with predefined data';

    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct('app:create-test-site');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $site = new Site();
        $site->setDomain('test-site.com');
        $site->setHosting('VPS-01');
        $site->setDatabaseName('db-testing');
        $site->setDatabasePassword('test1234');
        $site->setTechnology('WordPress');
        $site->setPublishedAt(new \DateTime('2026-03-13 10:00:00'));
        $site->setDefaultLocale('fr');
        $site->setIsActive(true);

        $this->entityManager->persist($site);
        $this->entityManager->flush();

        $output->writeln('Test site created successfully with ID: ' . $site->getId());

        return Command::SUCCESS;
    }
}
