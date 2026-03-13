<?php

require __DIR__.'/vendor/autoload.php';

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

// Create the Symfony application
$kernel = new App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

$container = $kernel->getContainer();

// Get the entity manager
$entityManager = $container->get(EntityManagerInterface::class);

// Find the admin user
$user = $entityManager->getRepository(User::class)->findOneBy(['name' => 'admin']);

if (!$user) {
    echo "User not found.\n";
    exit(1);
}

// Hash the password
$passwordHasher = $container->get(Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class);
$hashedPassword = $passwordHasher->hashPassword($user, 'admin123');

// Update the user's password
$user->setPassword($hashedPassword);

// Save to database
$entityManager->flush();

echo "Password updated successfully.\n";
