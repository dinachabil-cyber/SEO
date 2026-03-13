<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminUserCommand extends Command
{
    protected static $defaultName = 'app:create-admin-user';
    protected static $defaultDescription = 'Create a new admin user';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription(self::$defaultDescription)
            ->addArgument('name', InputArgument::REQUIRED, 'The username of the admin user')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the admin user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');
        $plainPassword = $input->getArgument('password');

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['name' => $name]);
        if ($existingUser) {
            $io->error(sprintf('User with name "%s" already exists', $name));
            return Command::FAILURE;
        }

        $user = new User();
        $user->setName($name);
        $user->setRoles(['ROLE_ADMIN']);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Admin user "%s" created successfully', $name));

        return Command::SUCCESS;
    }
}
