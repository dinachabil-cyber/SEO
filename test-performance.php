<?php
require __DIR__.'/vendor/autoload.php';

// Create a simple script to test Symfony initialization time
echo "Testing Symfony initialization time...\n\n";

$start = microtime(true);

// Boot the kernel
$kernel = new \App\Kernel('dev', true);
$kernel->boot();

$bootTime = microtime(true) - $start;
echo "Kernel boot time: " . round($bootTime * 1000, 2) . " ms\n";

// Create a simple request
$request = \Symfony\Component\HttpFoundation\Request::create('/', 'GET');

// Handle the request with a simple controller (we'll create one temporarily)
$response = $kernel->handle($request);

// Send the response (we won't actually send it, but just measure time)
$response->prepare($request);

$totalTime = microtime(true) - $start;
echo "Total request time: " . round($totalTime * 1000, 2) . " ms\n";
echo "Response status: " . $response->getStatusCode() . "\n";

// Terminate the kernel
$kernel->terminate($request, $response);
