<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h2>Syncing Jamendo Tracks...</h2><pre>";
$artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);
$exitCode = $artisan->call('jamendo:sync', ['--limit' => 200, '--total' => 500]);
echo nl2br($artisan->output());
echo "</pre><hr>";
echo "<p>Done! <a href='sync-audius.php'>Next: Sync Audius</a></p>";
