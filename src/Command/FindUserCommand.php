<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:find-user',
    description: 'Find a user by username',
)]
class FindUserCommand extends Command
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, 'The username to find')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $username = $input->getArgument('username');

        $user = $this->userRepository->findOneBy(['name' => $username]);

        if ($user) {
            $io->success(sprintf('Found user: %s (ID: %d)', $user->getName(), $user->getId()));
            $io->writeln('Roles: ' . implode(', ', $user->getRoles()));
            $io->writeln('Created: ' . $user->getCreatedAt()->format('Y-m-d H:i:s'));
        } else {
            $io->error(sprintf('User with username "%s" not found', $username));
        }

        return Command::SUCCESS;
    }
}
