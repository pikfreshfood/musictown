<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h2>Running Migrations...</h2><pre>";
$artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);
$exitCode = $artisan->call('migrate', ['--force' => true]);
echo nl2br($artisan->output());
echo "</pre><hr>";
echo "<p>Done! <a href='sync-jamendo.php'>Next: Sync Jamendo</a></p>";
