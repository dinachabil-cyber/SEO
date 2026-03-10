<?php

namespace App\Command;

use App\Entity\PageSection;
use App\Entity\ReferenceSection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SaveReferenceCommand extends Command
{
    protected static $defaultName = 'app:save-reference';
    protected static $defaultDescription = 'Save a page section as reference';

    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->addOption('section-id', null, InputOption::VALUE_REQUIRED, 'Section ID to save as reference')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Reference section name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sectionId = $input->getOption('section-id');
        if (!$sectionId) {
            $output->writeln('<error>Please provide --section-id</error>');
            return Command::FAILURE;
        }

        $section = $this->em->getRepository(PageSection::class)->find($sectionId);
        if (!$section) {
            $output->writeln("<error>Section with ID $sectionId not found</error>");
            return Command::FAILURE;
        }

        $referenceSection = new ReferenceSection();
        $referenceSection->setName($input->getOption('name') ?: ($section->getName() ?: 'Section ' . ucfirst($section->getType()) . ' (Reference)'));
        $referenceSection->setType($section->getType());
        $referenceSection->setData($section->getData());

        $this->em->persist($referenceSection);
        $this->em->flush();

        $output->writeln("<info>Reference section saved successfully</info>");
        $output->writeln("- ID: {$referenceSection->getId()}");
        $output->writeln("- Name: {$referenceSection->getName()}");
        $output->writeln("- Type: {$referenceSection->getType()}");

        return Command::SUCCESS;
    }
}
