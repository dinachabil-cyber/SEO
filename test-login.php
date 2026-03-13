<?php

require __DIR__.'/vendor/autoload.php';

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

$kernel = new App\Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$entityManager = $container->get(EntityManagerInterface::class);
$passwordHasher = $container->get(UserPasswordHasherInterface::class);

// Find user
$user = $entityManager->getRepository(User::class)->findOneBy(['name' => 'admin']);

if (!$user) {
    echo "User not found.\n";
    exit;
}

// Verify password
$isValid = $passwordHasher->isPasswordValid($user, 'admin123');
echo "Password is valid: " . ($isValid ? 'Yes' : 'No') . "\n";
echo "Stored password hash: " . $user->getPassword() . "\n";
