<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('migrate:fresh', ['--seed' => true, '--force' => true]);
echo "<pre>" . $kernel->output() . "</pre>";
echo "<h3>Setup complete!</h3>";
echo "<p>Login at <a href='/admin/login'>/admin/login</a> with <b>admin</b> / <b>admin</b></p>";
