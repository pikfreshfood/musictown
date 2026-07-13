<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@musictown.test')->first();
if ($user) {
    $user->update([
        'name' => 'admin',
        'password' => bcrypt('admin'),
        'is_admin' => true,
        'is_premium' => true,
        'role' => 'super_admin',
    ]);
} else {
    App\Models\User::create([
        'name' => 'admin',
        'email' => 'admin@musictown.test',
        'phone' => '0000000000',
        'password' => bcrypt('admin'),
        'balance' => 0,
        'is_admin' => true,
        'is_premium' => true,
        'role' => 'super_admin',
    ]);
}
echo "Done! Admin credentials set to: admin / admin. You can now login at /admin/login";
