<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:clear-form-fields',
    description: 'Clears default form fields from all form sections',
)]
class ClearFormFieldsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = $this->entityManager->getConnection();
        
        // Find all form sections
        $sql = "SELECT id, data FROM page_section WHERE type = 'form'";
        $stmt = $connection->prepare($sql);
        $sections = $stmt->executeQuery()->fetchAllAssociative();
        
        $output->writeln('Found ' . count($sections) . ' form sections');
        
        foreach ($sections as $section) {
            $data = json_decode($section['data'], true);
            
            // Check if form_fields exists
            if (isset($data['form_fields'])) {
                $output->writeln('Section ID ' . $section['id'] . ': Clearing form_fields');
                
                // Remove form_fields from data
                unset($data['form_fields']);
                
                // Update the database
                $connection->update('page_section', 
                    ['data' => json_encode($data)], 
                    ['id' => $section['id']]
                );
            }
        }
        
        $output->writeln('Done! Default form fields have been cleared.');
        
        return Command::SUCCESS;
    }
}
