<?php

namespace App\Command\App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[AsCommand(
    name: 'app:get-csrf-token',
    description: 'Get CSRF token for the registration form',
)]
class GetCsrfTokenCommand extends Command
{
    public function __construct(private readonly CsrfTokenManagerInterface $csrfTokenManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // The token id for the registration form
        $token = $this->csrfTokenManager->getToken('registration');
        
        $io->writeln('CSRF Token: ' . $token->getValue());

        return Command::SUCCESS;
    }
}
