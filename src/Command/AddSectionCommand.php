<?php

namespace App\Command;

use App\Entity\Page;
use App\Entity\PageSection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-section',
    description: 'Adds a section to a page',
)]
class AddSectionCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('pageId', InputArgument::REQUIRED, 'Page ID')
            ->addArgument('type', InputArgument::REQUIRED, 'Section type: header, body, cards, image, form, footer')
            ->addArgument('position', InputArgument::REQUIRED, 'Position')
            ->addArgument('data', InputArgument::REQUIRED, 'Section data as JSON')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $pageId = $input->getArgument('pageId');
        $type = $input->getArgument('type');
        $position = $input->getArgument('position');
        $data = $input->getArgument('data');

        // Find the page
        $page = $this->entityManager->getRepository(Page::class)->find($pageId);
        
        if (!$page) {
            $io->error("Page with ID $pageId not found");
            return Command::FAILURE;
        }

        // Validate section type
        $allowedTypes = ['header', 'body', 'cards', 'image', 'form', 'footer'];
        if (!in_array($type, $allowedTypes)) {
            $io->error("Invalid section type. Allowed types: " . implode(', ', $allowedTypes));
            return Command::FAILURE;
        }

        // Validate JSON data
        $decodedData = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error("Invalid JSON data: " . json_last_error_msg());
            return Command::FAILURE;
        }

        // Create section
        $section = new PageSection();
        $section->setPage($page);
        $section->setType($type);
        $section->setPosition((int)$position);
        $section->setData($decodedData);

        $this->entityManager->persist($section);
        $this->entityManager->flush();

        $io->success(sprintf('Section added to page "%s"', $page->getSlug()));

        return Command::SUCCESS;
    }
}
