<?php

/**
 * First-time setup — run ONCE after initial deploy.
 * Generates app key, runs migrations, seeds super admin.
 *
 * ACCESS THIS FILE VIA BROWSER, THEN DELETE IT IMMEDIATELY!
 */

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h2>🚀 Mulberry First-Time Setup</h2><pre>";

try {
    $kernel->call('key:generate', ['--force' => true]);
    echo "✅ APP_KEY generated\n";
} catch (Exception $e) {
    echo "❌ Key: " . $e->getMessage() . "\n";
}

try {
    // Create storage symlink
    $kernel->call('storage:link');
    echo "✅ Storage linked\n";
} catch (Exception $e) {
    echo "❌ Storage link: " . $e->getMessage() . "\n";
}

try {
    $kernel->call('migrate', ['--force' => true]);
    echo "✅ Database migrated\n";
} catch (Exception $e) {
    echo "❌ Migrate: " . $e->getMessage() . "\n";
}

try {
    $kernel->call('db:seed', ['--force' => true]);
    echo "✅ Super Admin seeded\n";
} catch (Exception $e) {
    echo "❌ Seed: " . $e->getMessage() . "\n";
}

try {
    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    echo "✅ Config/Route/View cached\n";
} catch (Exception $e) {
    echo "❌ Cache: " . $e->getMessage() . "\n";
}

echo "\n🎉 Setup selesai!\n";
echo "⚠️  HAPUS FILE INI SEKARANG dari File Manager cPanel!\n";
echo "    Path: public/first-setup.php\n";
echo "</pre>";
