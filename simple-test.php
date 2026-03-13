<?php
// Start time
$start = microtime(true);

// Simple test to measure PHP performance
for ($i = 0; $i < 100000; $i++) {
    $a = $i * $i;
}

// Stop time
$end = microtime(true);

// Calculate duration
$duration = $end - $start;

// Log to file
file_put_contents(__DIR__.'/simple-test.log', sprintf("Simple loop test: %.4f seconds\n", $duration), FILE_APPEND);

// Print to console
echo sprintf("Simple loop test: %.4f seconds\n", $duration);
