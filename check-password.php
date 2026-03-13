<?php
require 'vendor/autoload.php';

$kernel = new App\Kernel('dev', true);
$kernel->boot();
$em = $kernel->getContainer()->get('doctrine')->getManager();
$user = $em->getRepository(App\Entity\User::class)->find(2);

var_dump($user->getName());
var_dump($user->getPassword());

// Verify password
$passwordHasher = $kernel->getContainer()->get('security.password_hasher');
var_dump($passwordHasher->isPasswordValid($user, 'admin123'));
