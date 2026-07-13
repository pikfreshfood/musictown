<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureSqliteDatabaseIsReady();
        $this->ensureDefaultAdminAccount();
    }

    private function ensureSqliteDatabaseIsReady(): void
    {
        if ($this->app->runningInConsole() || config('database.default') !== 'sqlite') {
            return;
        }

        $database = config('database.connections.sqlite.database');

        if (!$database || $database === ':memory:') {
            return;
        }

        $directory = dirname($database);
        $created = false;

        if (!is_dir($directory) && !@mkdir($directory, 0755, true)) {
            return;
        }

        if (!file_exists($database)) {
            if (!@touch($database)) {
                return;
            }

            $created = true;
        }

        clearstatcache(true, $database);

        if ($created || @filesize($database) === 0 || $this->sqliteNeedsCoreTables()) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (Throwable) {
                // Let the original database error surface if the host cannot write or migrate.
            }
        }
    }

    private function sqliteNeedsCoreTables(): bool
    {
        try {
            return !Schema::hasTable('users') || !Schema::hasTable('sessions');
        } catch (Throwable) {
            return true;
        }
    }

    private function ensureDefaultAdminAccount(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        try {
            if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'is_admin')) {
                return;
            }

            $admin = User::where(function ($query) {
                $query->whereRaw('LOWER(name) = ?', ['admin'])
                    ->orWhereRaw('LOWER(email) = ?', ['admin@musictown.test']);
            })
                ->first();

            if (!$admin) {
                $admin = new User();
                $admin->email = 'admin@musictown.test';
                $admin->phone = User::generateHiddenPhone();
                $admin->balance = 0;
                $admin->forceFill([
                    'name' => 'admin',
                    'password' => Hash::make('admin'),
                    'is_admin' => true,
                    'is_premium' => true,
                    'role' => 'super_admin',
                ])->save();

                return;
            }

            $updates = [
                'name' => 'admin',
                'is_admin' => true,
                'is_premium' => true,
                'role' => 'super_admin',
            ];

            if (!Hash::check('admin', $admin->password)) {
                $updates['password'] = Hash::make('admin');
            }

            $admin->forceFill($updates);

            if ($admin->isDirty()) {
                $admin->save();
            }
        } catch (Throwable) {
            // Keep the app booting if a host has a temporarily unavailable database.
        }
    }
}
