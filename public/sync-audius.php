<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "<h2>Syncing Audius Tracks...</h2><pre>";
$artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);
$exitCode = $artisan->call('audius:sync');
echo nl2br($artisan->output());
echo "</pre><hr>";
echo "<p>All done! <strong>Delete these files from public/ for security.</strong></p>";
