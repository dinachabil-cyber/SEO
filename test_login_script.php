<?php
// This is a simple script to test the login functionality
// Run it directly on the server

require_once __DIR__.'/vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

// Create a kernel instance
$kernel = new \App\Kernel('dev', true);
$kernel->boot();

// Get the security token storage and user repository
$entityManager = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');
$passwordHasher = $kernel->getContainer()->get('Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface');
$userRepository = $entityManager->getRepository('App\Entity\User');

// Find the user
$user = $userRepository->findOneBy(['name' => 'dina chabil']);

if (!$user) {
    die("User not found.\n");
}

// Check if password is admin123
if ($passwordHasher->isPasswordValid($user, 'admin123')) {
    echo "✅ Password is valid for user: " . $user->getName() . "\n";
    echo "Roles: " . implode(', ', $user->getRoles()) . "\n";
} else {
    echo "❌ Invalid password.\n";
}

// Try to get the CSRF token for authentication
try {
    $csrfToken = $kernel->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
    echo "\n✅ CSRF Token: " . $csrfToken->getValue() . "\n";
} catch (\Exception $e) {
    echo "\n❌ Error getting CSRF token: " . $e->getMessage() . "\n";
}

// Shutdown the kernel
$kernel->shutdown();
