<?php
// Read and parse .env file
$envContent = file_get_contents(__DIR__ . '/.env');
$lines = explode("\n", $envContent);
$envVars = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (empty($line) || strpos($line, '#') === 0) continue;
    
    list($key, $value) = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);
    // Remove quotes
    if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
        $value = substr($value, 1, -1);
    }
    $envVars[$key] = $value;
}

// Parse DATABASE_URL
$url = parse_url($envVars['DATABASE_URL']);
$dbUser = $url['user'];
$dbPass = $url['pass'];
$dbHost = $url['host'];
$dbPort = $url['port'] ?? 3306;
$dbName = ltrim($url['path'], '/');

// Create database connection
$pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass);

// Check if page exists
$slug = 'uuuuuu';
$stmt = $pdo->prepare('SELECT * FROM page WHERE slug = ?');
$stmt->execute([$slug]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if ($page) {
    echo "Page found! ID: " . $page['id'] . "\n";
    echo "Title: " . $page['meta_title'] . "\n";
    echo "Published: " . ($page['is_published'] ? "Yes" : "No") . "\n";
    echo "Site ID: " . $page['site_id'] . "\n";
} else {
    echo "Page not found!";
}

// List all pages
echo "\nAll pages in database:\n";
$stmt = $pdo->query('SELECT id, slug, is_published, site_id FROM page');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- ID: " . $row['id'] . ", Slug: " . $row['slug'] . ", Published: " . ($row['is_published'] ? "Yes" : "No") . ", Site ID: " . $row['site_id'] . "\n";
}

// Check sites
echo "\nSites in database:\n";
$stmt = $pdo->query('SELECT id, is_active FROM site');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "- ID: " . $row['id'] . ", Active: " . ($row['is_active'] ? "Yes" : "No") . "\n";
}
