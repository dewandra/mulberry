<?php

/**
 * Deploy Webhook — runs artisan commands after FTP deploy.
 * Protected by a secret token. Called automatically by GitHub Actions.
 *
 * IMPORTANT: Set DEPLOY_TOKEN in your .env file on the server!
 */

// Load Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Verify token
$expectedToken = env('DEPLOY_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$expectedToken || $providedToken !== $expectedToken) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Optional: update APP_VERSION in .env
$version = $_GET['version'] ?? null;
if ($version) {
    $envPath = base_path('.env');
    $envContent = file_get_contents($envPath);
    if (str_contains($envContent, 'APP_VERSION=')) {
        $envContent = preg_replace('/^APP_VERSION=.*/m', "APP_VERSION={$version}", $envContent);
    } else {
        $envContent .= "\nAPP_VERSION={$version}";
    }
    file_put_contents($envPath, $envContent);
}

// Run artisan commands
$results = [];

try {
    $kernel->call('migrate', ['--force' => true]);
    $results[] = '✅ Migrated';
} catch (Exception $e) {
    $results[] = '❌ Migrate: ' . $e->getMessage();
}

try {
    $kernel->call('config:cache');
    $results[] = '✅ Config cached';
} catch (Exception $e) {
    $results[] = '❌ Config cache: ' . $e->getMessage();
}

try {
    $kernel->call('route:cache');
    $results[] = '✅ Routes cached';
} catch (Exception $e) {
    $results[] = '❌ Route cache: ' . $e->getMessage();
}

try {
    $kernel->call('view:cache');
    $results[] = '✅ Views cached';
} catch (Exception $e) {
    $results[] = '❌ View cache: ' . $e->getMessage();
}

try {
    $kernel->call('app:clear-cache');
    $results[] = '✅ App cache cleared';
} catch (Exception $e) {
    $results[] = '❌ App cache: ' . $e->getMessage();
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'version' => $version,
    'results' => $results,
]);
