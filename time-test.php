<?php

// Start time
$start = microtime(true);

// Require composer autoloader
require __DIR__.'/vendor/autoload.php';

// Initialize Symfony kernel
$kernel = new \App\Kernel('dev', true);
$kernel->boot();

// Create a simple request
$request = \Symfony\Component\HttpFoundation\Request::create('/test');

// Handle the request
$response = $kernel->handle($request);

// Send response (we won't actually send it, but we'll measure time to handle)
$response->send();

// Stop time
$end = microtime(true);

// Calculate duration
$duration = $end - $start;

// Log to file
file_put_contents(__DIR__.'/time-test.log', sprintf("Time taken: %.4f seconds\n", $duration), FILE_APPEND);

// Print to console
echo sprintf("Time taken: %.4f seconds\n", $duration);

// Shutdown kernel
$kernel->shutdown();
